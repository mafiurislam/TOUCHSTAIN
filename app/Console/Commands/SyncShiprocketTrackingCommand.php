<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Services\Delivery\DeliveryManager;
use Illuminate\Console\Command;

class SyncShiprocketTrackingCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'shiprocket:sync-tracking';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize live tracking events for all in-transit Shiprocket shipments';

    /**
     * Execute the console command.
     */
    public function handle(DeliveryManager $deliveryManager)
    {
        $this->info('Starting Shiprocket tracking synchronization...');

        $activeShipments = Shipment::whereNotIn('status', ['delivered', 'cancelled', 'rto_delivered', 'lost'])
            ->whereNotNull('awb_number')
            ->get();

        $this->info("Found {$activeShipments->count()} active shipments to check.");

        $synced = 0;
        $driver = $deliveryManager->driver();

        foreach ($activeShipments as $shipment) {
            try {
                $res = $driver->trackShipment($shipment);
                if ($res['success']) {
                    $this->line("✓ Synced Shipment #{$shipment->id} (AWB: {$shipment->awb_number}) -> {$shipment->fresh()->formatted_status}");
                    $synced++;
                } else {
                    $this->warn("⚠ Failed to sync Shipment #{$shipment->id}: " . ($res['message'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $this->error("✗ Error syncing Shipment #{$shipment->id}: {$e->getMessage()}");
            }
        }

        $this->info("Completed. Successfully synchronized {$synced} shipments.");
        return Command::SUCCESS;
    }
}
