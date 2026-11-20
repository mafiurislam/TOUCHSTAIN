<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DeliverySetting;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\Product;
use App\Services\Delivery\DeliveryManager;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Orders listing with comprehensive filters
     */
    public function index(Request $request)
    {
        $query = Order::with(['items', 'user', 'shipment'])->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('order_status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('id', $s)
                  ->orWhere('contact_person_name', 'like', "%{$s}%")
                  ->orWhere('contact_person_number', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $orders = $query->paginate(20)->withQueryString();

        $statusCounts = [
            'all' => Order::count(),
            'pending' => Order::where('order_status', 'pending')->count(),
            'confirmed' => Order::where('order_status', 'confirmed')->count(),
            'processing' => Order::where('order_status', 'processing')->count(),
            'out_for_delivery' => Order::where('order_status', 'out_for_delivery')->count(),
            'delivered' => Order::where('order_status', 'delivered')->count(),
            'canceled' => Order::where('order_status', 'canceled')->count(),
            'returned' => Order::where('order_status', 'returned')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * View detailed order information
     */
    public function show($id)
    {
        $order = Order::with(['items', 'user', 'shipment.trackingEvents', 'bankTransfer.logs'])->findOrFail($id);
        $deliverySettings = DeliverySetting::getOrCreate();

        return view('admin.orders.show', compact('order', 'deliverySettings'));
    }

    /**
     * Update order status with stock and tracking synchronization
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);
        $oldStatus = $order->order_status;
        $newStatus = $request->input('order_status', $oldStatus);
        $paymentStatus = $request->input('payment_status', $order->payment_status);
        $adminNote = $request->input('admin_note');

        $order->update([
            'order_status' => $newStatus,
            'payment_status' => $paymentStatus,
            'order_note' => $adminNote ?: $order->order_note,
        ]);

        // Stock Restoration if order is canceled or returned
        if (in_array($newStatus, ['canceled', 'returned']) && !in_array($oldStatus, ['canceled', 'returned'])) {
            foreach ($order->items as $item) {
                $p = Product::find($item->product_id);
                if ($p) {
                    $p->increment('total_stock', $item->quantity);
                    InventoryLog::create([
                        'product_id' => $p->id,
                        'type' => 'order_restore',
                        'quantity' => $item->quantity,
                        'current_stock' => $p->total_stock,
                        'notes' => "Stock restored from canceled/returned Order #{$order->id}",
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        }

        $msg = "Order #{$id} updated to " . str_replace('_', ' ', $newStatus) . "!";

        // Automatically send order to Shiprocket when confirmed or processing
        if (in_array($newStatus, ['confirmed', 'processing'])) {
            try {
                $deliveryManager = app(\App\Services\Delivery\DeliveryManager::class);
                $res = $deliveryManager->driver()->createShipment($order);
                if (!empty($res['success'])) {
                    $msg .= " 🚚 Order sent to Shiprocket & added to Delivery Hub!";
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Shiprocket create shipment on status update notice: ' . $e->getMessage());
            }
        }

        AuditLog::record('Orders', 'Update Status', "Order [#{$id}] status changed from '{$oldStatus}' to '{$newStatus}'");

        return back()->with('success', $msg);
    }

    /**
     * Professional Printable Invoice
     */
    public function invoice($id)
    {
        $order = Order::with(['items', 'user', 'shipment'])->findOrFail($id);
        return view('admin.orders.invoice', compact('order'));
    }

    /**
     * Bulk update orders status
     */
    public function bulkStatus(Request $request)
    {
        $orderIds = $request->input('order_ids', []);
        $newStatus = $request->input('bulk_status');

        if (empty($orderIds) || empty($newStatus)) {
            return back()->with('error', 'Please select at least one order and a target status.');
        }

        Order::whereIn('id', $orderIds)->update(['order_status' => $newStatus]);
        AuditLog::record('Orders', 'Bulk Status Update', "Updated " . count($orderIds) . " orders to {$newStatus}");

        return back()->with('success', "Successfully updated " . count($orderIds) . " orders to {$newStatus}!");
    }

    /**
     * Delete an order and its associated shipment and tracking data
     */
    public function destroy(Request $request, $id)
    {
        $order = Order::with(['items', 'shipment.trackingEvents', 'bankTransfer'])->findOrFail($id);

        // If shipment exists on Shiprocket, attempt cancellation gracefully
        if ($order->shipment && !empty($order->shipment->shiprocket_order_id)) {
            try {
                $deliveryManager = app(\App\Services\Delivery\DeliveryManager::class);
                $deliveryManager->driver()->cancelShipment($order->shipment);
            } catch (\Throwable $e) {
                // Ignore Shiprocket cancellation error and proceed with deletion
            }
        }

        // Delete associated tracking events and shipment
        if ($order->shipment) {
            $order->shipment->trackingEvents()->delete();
            $order->shipment->delete();
        }

        // Delete order items
        $order->items()->delete();

        // Delete bank transfer if any
        if ($order->bankTransfer) {
            $order->bankTransfer->delete();
        }

        $orderId = $order->id;
        $order->delete();

        AuditLog::record('Orders', 'Delete Order', "Order #{$orderId} deleted by admin");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => "Order #{$orderId} deleted successfully.",
            ]);
        }

        return redirect()->route('admin.orders')->with('success', "Order #{$orderId} deleted successfully!");
    }
}
