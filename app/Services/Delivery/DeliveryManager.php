<?php

namespace App\Services\Delivery;

use App\Models\DeliverySetting;
use App\Models\Order;
use App\Models\Shipment;

class DeliveryManager
{
    protected ?CourierServiceInterface $driver = null;
    protected DeliverySetting $settings;

    public function __construct()
    {
        $this->settings = DeliverySetting::getOrCreate();
    }

    /**
     * Get the active courier service driver
     */
    public function driver(?string $provider = null): CourierServiceInterface
    {
        $providerName = $provider ?: $this->settings->provider;

        if ($this->driver === null) {
            $this->driver = match (strtolower($providerName)) {
                'shiprocket' => new ShiprocketService($this->settings),
                default => new ShiprocketService($this->settings),
            };
        }

        return $this->driver;
    }

    /**
     * Check serviceability for an order
     */
    public function checkOrderServiceability(Order $order, ?float $customWeight = null): array
    {
        $pickupPincode = $this->settings->warehouse_pincode ?: '400022';
        $deliveryPincode = $order->getDeliveryPincode();
        $weight = $customWeight ?: $order->calculateTotalWeight();
        $isCod = $order->isCod();
        $orderAmount = (float)$order->order_amount;

        return $this->driver()->checkServiceability(
            $pickupPincode,
            $deliveryPincode,
            $weight,
            $isCod,
            $orderAmount
        );
    }

    /**
     * Complete lifecycle creation: Order -> Shipment -> AWB -> Label -> Pickup
     */
    public function processFullShipment(Order $order, array $options = []): array
    {
        $service = $this->driver();

        // 1. Create Shipment
        $result = $service->createShipment($order, $options);
        if (!$result['success']) {
            return $result;
        }

        /** @var Shipment $shipment */
        $shipment = $result['shipment'];

        // 2. Generate AWB if not yet generated
        if (empty($shipment->awb_number)) {
            $courierId = $options['courier_company_id'] ?? null;
            $awbRes = $service->generateAwb($shipment, $courierId ? (int)$courierId : null);
            if ($awbRes['success']) {
                $shipment = $shipment->fresh();
            }
        }

        // 3. Generate Label
        $service->generateLabel($shipment);

        // 4. Schedule Pickup if requested
        if (!empty($options['schedule_pickup']) || $this->settings->auto_schedule_pickup) {
            $pickupDate = $options['pickup_date'] ?? null;
            $service->schedulePickup($shipment, $pickupDate);
        }

        return [
            'success' => true,
            'message' => 'Shipment successfully created and processed.',
            'shipment' => $shipment->fresh(),
        ];
    }
}
