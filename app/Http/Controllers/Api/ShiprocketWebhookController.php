<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryApiLog;
use App\Models\DeliverySetting;
use App\Models\Shipment;
use App\Models\ShipmentTrackingEvent;
use App\Services\Delivery\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiprocketWebhookController extends Controller
{
    /**
     * Inbound Webhook Listener for Shiprocket Tracking Updates
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $settings = DeliverySetting::getOrCreate();

        // Optional token verification if webhook_token is configured
        $headerToken = $request->header('x-shiprocket-token') ?: $request->header('X-Api-Key');
        if ($settings->webhook_token && $headerToken && $headerToken !== $settings->webhook_token) {
            Log::warning('Shiprocket Webhook Unauthorized: Token mismatch', ['header' => $headerToken]);
            return response()->json(['status' => false, 'message' => 'Unauthorized webhook signature'], 401);
        }

        // Log the inbound webhook payload
        DeliveryApiLog::create([
            'provider' => 'shiprocket',
            'endpoint' => '/api/v1/delivery/webhook/shiprocket',
            'method' => 'POST',
            'request_payload' => $payload,
            'response_payload' => ['status' => 'received'],
            'status_code' => 200,
            'is_success' => true,
        ]);

        $awb = $payload['awb'] ?? ($payload['awb_code'] ?? null);
        $shipmentId = $payload['shipment_id'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $currentStatus = $payload['current_status'] ?? ($payload['status'] ?? null);
        $location = $payload['location'] ?? 'In Transit Hub';
        $activity = $payload['activity'] ?? ($payload['scans'][0]['activity'] ?? "Status updated: {$currentStatus}");

        if (!$awb && !$shipmentId && !$orderId) {
            return response()->json(['status' => false, 'message' => 'Missing shipment identifier'], 400);
        }

        // Locate shipment
        $shipment = Shipment::where(function ($q) use ($awb, $shipmentId, $orderId) {
            if ($awb) $q->where('awb_number', $awb);
            if ($shipmentId) $q->orWhere('shiprocket_shipment_id', (string)$shipmentId);
            if ($orderId) {
                // Remove prefix e.g. ORD-100248-1234
                if (preg_match('/ORD-(\d+)/', $orderId, $matches)) {
                    $q->orWhere('order_id', $matches[1]);
                } else {
                    $q->orWhere('order_id', $orderId);
                }
            }
        })->first();

        if (!$shipment) {
            Log::info('Shiprocket Webhook: Shipment not found', ['payload' => $payload]);
            return response()->json(['status' => true, 'message' => 'Received but no matching shipment found']);
        }

        $service = new ShiprocketService($settings);
        $normalizedStatus = $service->mapShiprocketStatus((string)$currentStatus);

        // Update shipment status
        $updateData = [
            'status' => $normalizedStatus,
            'sub_status' => (string)$currentStatus,
        ];
        if (!empty($payload['courier_name'])) {
            $updateData['courier_name'] = $payload['courier_name'];
        }
        if (!empty($payload['etd'])) {
            $updateData['etd_date'] = now()->parse($payload['etd']);
        }
        if ($normalizedStatus === 'delivered') {
            $updateData['delivered_at'] = now();
        }

        $shipment->update($updateData);

        // Duplicate event protection: check if same event recorded in last 5 minutes
        $recentDuplicate = ShipmentTrackingEvent::where('shipment_id', $shipment->id)
            ->where('status', $normalizedStatus)
            ->where('activity', $activity)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if (!$recentDuplicate) {
            ShipmentTrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $normalizedStatus,
                'activity' => $activity,
                'location' => $location,
                'event_time' => !empty($payload['date']) ? now()->parse($payload['date']) : now(),
                'raw_payload' => $payload,
            ]);
        }

        // Sync order status
        $order = $shipment->order;
        if ($order) {
            if ($normalizedStatus === 'delivered' && $order->order_status !== 'delivered') {
                $order->update(['order_status' => 'delivered', 'payment_status' => 'paid']);
            } elseif ($normalizedStatus === 'out_for_delivery' && $order->order_status !== 'out_for_delivery') {
                $order->update(['order_status' => 'out_for_delivery']);
            } elseif (in_array($normalizedStatus, ['in_transit', 'picked_up']) && !in_array($order->order_status, ['out_for_delivery', 'delivered'])) {
                $order->update(['order_status' => 'processing']);
            } elseif ($normalizedStatus === 'cancelled' && $order->order_status !== 'canceled') {
                $order->update(['order_status' => 'canceled']);
            }
        }

        return response()->json([
            'status' => true,
            'message' => "Shipment #{$shipment->id} status successfully updated to {$normalizedStatus}",
        ]);
    }
}
