<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DeliveryApiLog;
use App\Models\DeliverySetting;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentReturn;
use App\Models\ShipmentTrackingEvent;
use App\Services\Delivery\DeliveryManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{
    protected DeliveryManager $deliveryManager;

    public function __construct(DeliveryManager $deliveryManager)
    {
        $this->deliveryManager = $deliveryManager;
    }

    /**
     * Delivery Management Dashboard
     */
    public function dashboard(Request $request)
    {
        $settings = DeliverySetting::getOrCreate();

        $query = Shipment::with(['order.user', 'latestTrackingEvent'])->orderByDesc('id');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }
        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('awb_number', 'like', "%{$s}%")
                  ->orWhere('shiprocket_order_id', 'like', "%{$s}%")
                  ->orWhere('order_id', $s)
                  ->orWhere('courier_name', 'like', "%{$s}%")
                  ->orWhereHas('order', function ($oq) use ($s) {
                      $oq->where('contact_person_name', 'like', "%{$s}%")
                         ->orWhere('contact_person_number', 'like', "%{$s}%")
                         ->orWhereHas('user', function ($uq) use ($s) {
                             $uq->where('f_name', 'like', "%{$s}%")
                                ->orWhere('l_name', 'like', "%{$s}%")
                                ->orWhere('phone', 'like', "%{$s}%");
                         });
                  });
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $shipments = $query->paginate(20)->withQueryString();

        // Calculate KPI Counts
        $counts = [
            'total' => Shipment::count(),
            'pending' => Shipment::where('status', 'pending')->count(),
            'awb_assigned' => Shipment::where('status', 'awb_assigned')->count(),
            'pickup_scheduled' => Shipment::where('status', 'pickup_scheduled')->count(),
            'in_transit' => Shipment::whereIn('status', ['picked_up', 'in_transit', 'reached_hub'])->count(),
            'out_for_delivery' => Shipment::where('status', 'out_for_delivery')->count(),
            'delivered' => Shipment::where('status', 'delivered')->count(),
            'cancelled' => Shipment::where('status', 'cancelled')->count(),
            'rto' => Shipment::whereIn('status', ['rto_initiated', 'rto_delivered'])->count(),
            'ndr' => Shipment::whereIn('status', ['ndr', 'delayed', 'undelivered', 'lost'])->count(),
        ];

        // Orders pending shipment creation
        $ordersPendingShipment = Order::whereNotIn('order_status', ['delivered', 'canceled', 'unconfirmed'])
            ->whereDoesntHave('shipment', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            })
            ->orderByDesc('id')
            ->take(15)
            ->get();

        return view('admin.delivery.dashboard', compact('settings', 'shipments', 'counts', 'ordersPendingShipment'));
    }

    /**
     * Shiprocket Integration Settings
     */
    public function settings()
    {
        $settings = DeliverySetting::getOrCreate();
        $logs = DeliveryApiLog::where('provider', 'shiprocket')->orderByDesc('id')->take(30)->get();
        $pickupLocations = [];

        try {
            $driver = $this->deliveryManager->driver();
            if (method_exists($driver, 'getPickupLocations')) {
                $pickupLocations = $driver->getPickupLocations();
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        return view('admin.delivery.settings', compact('settings', 'logs', 'pickupLocations'));
    }

    /**
     * Update Delivery Settings
     */
    public function updateSettings(Request $request)
    {
        $settings = DeliverySetting::getOrCreate();

        $data = $request->validate([
            'provider' => 'required|string',
            'api_email' => 'nullable|string',
            'api_password' => 'nullable|string',
            'environment' => 'required|in:test,production',
            'pickup_location_name' => 'nullable|string',
            'warehouse_name' => 'nullable|string',
            'warehouse_address' => 'nullable|string',
            'warehouse_city' => 'nullable|string',
            'warehouse_state' => 'nullable|string',
            'warehouse_pincode' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'gst_number' => 'nullable|string',
            'default_weight' => 'nullable|numeric|min:0.01',
            'default_length' => 'nullable|numeric|min:1',
            'default_breadth' => 'nullable|numeric|min:1',
            'default_height' => 'nullable|numeric|min:1',
        ]);

        $data['auto_shipment_on_confirm'] = $request->has('auto_shipment_on_confirm');
        $data['auto_courier_selection'] = $request->has('auto_courier_selection');
        $data['auto_generate_awb'] = $request->has('auto_generate_awb');
        $data['auto_schedule_pickup'] = $request->has('auto_schedule_pickup');
        $data['is_active'] = $request->has('is_active');

        if (!empty($data['api_password']) && $data['api_password'] !== $settings->api_password) {
            $data['api_token'] = null;
            $data['token_expires_at'] = null;
        }

        $settings->update($data);

        AuditLog::record('Delivery Settings', 'Updated', 'Shiprocket settings updated by admin');

        return redirect()->route('admin.delivery.settings')->with('success', 'Shiprocket Delivery Settings successfully saved!');
    }

    /**
     * Test Shiprocket API Connection
     */
    public function testConnection()
    {
        $res = $this->deliveryManager->driver()->testConnection();
        return response()->json($res);
    }

    /**
     * Register Pickup Address on Shiprocket
     */
    public function registerPickup(Request $request)
    {
        $settings = DeliverySetting::getOrCreate();
        $driver = $this->deliveryManager->driver();

        if (method_exists($driver, 'registerPickupLocation')) {
            $res = $driver->registerPickupLocation([
                'nickname' => $request->input('pickup_location_name') ?: $settings->pickup_location_name,
                'name' => $request->input('contact_person') ?: $settings->contact_person,
                'email' => $request->input('api_email') ?: $settings->api_email,
                'phone' => $request->input('contact_number') ?: $settings->contact_number,
                'address' => $request->input('warehouse_address') ?: $settings->warehouse_address,
                'city' => $request->input('warehouse_city') ?: $settings->warehouse_city,
                'state' => $request->input('warehouse_state') ?: $settings->warehouse_state,
                'pincode' => $request->input('warehouse_pincode') ?: $settings->warehouse_pincode,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($res);
            }

            return back()->with($res['success'] ? 'success' : 'error', $res['message']);
        }

        return back()->with('error', 'Pickup registration not supported by active driver.');
    }

    /**
     * Check Real-Time Courier Rates & Serviceability
     */
    public function checkServiceability(Request $request)
    {
        $request->validate([
            'delivery_pincode' => 'required|string',
        ]);

        $settings = DeliverySetting::getOrCreate();
        $pickupPincode = $request->input('pickup_pincode', $settings->warehouse_pincode ?: '400074');
        $deliveryPincode = $request->input('delivery_pincode');
        $weight = (float)$request->input('weight', 0.5);
        $isCod = (bool)$request->input('is_cod', false);
        $declaredValue = (float)$request->input('declared_value', 100);

        $res = $this->deliveryManager->driver()->checkServiceability(
            $pickupPincode,
            $deliveryPincode,
            $weight,
            $isCod,
            $declaredValue
        );

        return response()->json($res);
    }

    /**
     * Create Shipment for an Order (Send to Shiprocket)
     */
    public function createShipment(Request $request, $orderId)
    {
        $order = Order::with('items')->findOrFail($orderId);

        $options = [
            'courier_company_id' => $request->input('courier_company_id'),
            'weight' => $request->input('weight'),
            'length' => $request->input('length'),
            'breadth' => $request->input('breadth'),
            'height' => $request->input('height'),
            'ship_now' => $request->has('ship_now') || $request->input('action') === 'ship_now',
        ];

        $res = $this->deliveryManager->driver()->createShipment($order, $options);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        if ($res['success']) {
            $msg = $res['message'] ?? "Order #{$order->id} successfully sent to Shiprocket!";
            return back()->with('success', $msg);
        }

        return back()->with('error', $res['message'] ?? 'Failed to create shipment.');
    }

    /**
     * Ship Now / Assign Courier & Generate AWB
     */
    public function generateAwb(Request $request, $shipmentId)
    {
        return $this->shipNow($request, $shipmentId);
    }

    /**
     * Ship Now: Assign Courier partner, Book AWB and Schedule Pickup
     */
    public function shipNow(Request $request, $shipmentId)
    {
        $shipment = Shipment::with('order')->findOrFail($shipmentId);
        $courierId = $request->input('courier_company_id') ? (int)$request->input('courier_company_id') : null;

        $res = $this->deliveryManager->driver()->generateAwb($shipment, $courierId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        if ($res['success']) {
            $courier = $res['courier'] ?? 'Courier Partner';
            $awb = $res['awb'] ?? 'Assigned';
            return back()->with('success', "🚀 Order #{$shipment->order_id} booked with {$courier}! AWB: {$awb}. Courier pickup scheduled!");
        }

        return back()->with('error', $res['message'] ?? 'Failed to book courier.');
    }

    /**
     * Generate Shipping Label
     */
    public function generateLabel(Request $request, $shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $res = $this->deliveryManager->driver()->generateLabel($shipment);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        return redirect($res['label_url'] ?? url("/admin/delivery/shipments/{$shipment->id}/print-label"));
    }

    /**
     * Printable Shipping Label
     */
    public function printLabel(Request $request, $shipmentId)
    {
        $shipment = Shipment::with(['order.items', 'order.user'])->findOrFail($shipmentId);
        $settings = DeliverySetting::getOrCreate();

        return view('admin.delivery.print-label', compact('shipment', 'settings'));
    }

    /**
     * Printable Manifest
     */
    public function printManifest(Request $request, $shipmentId)
    {
        $shipment = Shipment::with(['order.items', 'order.user'])->findOrFail($shipmentId);
        $settings = DeliverySetting::getOrCreate();

        return view('admin.delivery.print-manifest', compact('shipment', 'settings'));
    }

    /**
     * Schedule Courier Pickup
     */
    public function schedulePickup(Request $request, $shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $pickupDate = $request->input('pickup_date');

        $res = $this->deliveryManager->driver()->schedulePickup($shipment, $pickupDate);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        return back()->with($res['success'] ? 'success' : 'error', $res['message']);
    }

    /**
     * Refresh Live Tracking
     */
    public function trackShipment(Request $request, $shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $res = $this->deliveryManager->driver()->trackShipment($shipment);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        return back()->with('success', "Tracking refreshed for Shipment #{$shipmentId}.");
    }

    /**
     * Cancel Shipment
     */
    public function cancelShipment(Request $request, $shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $res = $this->deliveryManager->driver()->cancelShipment($shipment);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        return back()->with('success', $res['message']);
    }

    /**
     * Create Return Shipment
     */
    public function createReturn(Request $request, $shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $reason = $request->input('reason', 'Customer return requested');

        $res = $this->deliveryManager->driver()->createReturnShipment($shipment, $reason);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        return back()->with('success', $res['message']);
    }

    /**
     * Sync All Active Shipments Tracking
     */
    public function syncAllTracking()
    {
        $activeShipments = Shipment::whereNotIn('status', ['delivered', 'cancelled', 'lost'])->get();
        $count = 0;

        foreach ($activeShipments as $s) {
            if ($s->awb_number) {
                $this->deliveryManager->driver()->trackShipment($s);
                $count++;
            }
        }

        return back()->with('success', "Successfully synced live tracking for {$count} shipments from Shiprocket.");
    }

    /**
     * Export Shipments to CSV
     */
    public function exportShipments()
    {
        $shipments = Shipment::with(['order.user'])->orderByDesc('id')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="shiprocket_shipments_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($shipments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Shipment ID', 'Order ID', 'SR Order ID', 'AWB Number', 'Courier Partner', 'Status', 'Weight (KG)', 'Payment', 'COD Amount', 'Created At']);

            foreach ($shipments as $s) {
                fputcsv($file, [
                    $s->id,
                    $s->order_id,
                    $s->shiprocket_order_id,
                    $s->awb_number,
                    $s->courier_name,
                    $s->status,
                    $s->package_weight,
                    $s->payment_method,
                    $s->cod_amount,
                    $s->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
