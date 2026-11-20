<?php

namespace App\Services\Delivery;

use App\Models\Order;
use App\Models\Shipment;

interface CourierServiceInterface
{
    /**
     * Authenticate with provider and retrieve / cache bearer token
     */
    public function authenticate(bool $forceRefresh = false): bool;

    /**
     * Test connection to provider API
     */
    public function testConnection(): array;

    /**
     * Check pincode serviceability & compare courier freight rates
     */
    public function checkServiceability(
        string $pickupPincode,
        string $deliveryPincode,
        float $weight = 0.5,
        bool $isCod = false,
        float $declaredValue = 0.0
    ): array;

    /**
     * Create shipment / order in provider system
     */
    public function createShipment(Order $order, array $customOptions = []): array;

    /**
     * Assign courier & generate AWB code
     */
    public function generateAwb(Shipment $shipment, ?int $courierCompanyId = null): array;

    /**
     * Generate printable PDF shipping label
     */
    public function generateLabel(Shipment $shipment): array;

    /**
     * Generate shipment manifest PDF
     */
    public function generateManifest(Shipment $shipment): array;

    /**
     * Schedule courier pickup
     */
    public function schedulePickup(Shipment $shipment, ?string $pickupDate = null): array;

    /**
     * Fetch real-time live tracking data
     */
    public function trackShipment(Shipment $shipment): array;

    /**
     * Cancel shipment
     */
    public function cancelShipment(Shipment $shipment): array;

    /**
     * Create reverse pickup / return order
     */
    public function createReturnShipment(Shipment $shipment, string $reason, array $pickupAddress = []): array;
}
