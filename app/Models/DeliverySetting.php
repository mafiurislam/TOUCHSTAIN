<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverySetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'default_weight' => 'float',
        'default_length' => 'float',
        'default_breadth' => 'float',
        'default_height' => 'float',
        'auto_shipment_on_confirm' => 'boolean',
        'auto_courier_selection' => 'boolean',
        'auto_generate_awb' => 'boolean',
        'auto_schedule_pickup' => 'boolean',
        'is_active' => 'boolean',
        'token_expires_at' => 'datetime',
        'last_connected_at' => 'datetime',
    ];

    /**
     * Singleton helper to get or initialize default settings
     */
    public static function getOrCreate(): self
    {
        $setting = static::first();
        if (!$setting) {
            $setting = static::create([
                'provider' => 'shiprocket',
                'api_email' => env('SHIPROCKET_API_EMAIL', ''),
                'api_password' => env('SHIPROCKET_API_PASSWORD', ''),
                'environment' => env('SHIPROCKET_ENV', 'test'),
                'pickup_location_name' => env('SHIPROCKET_PICKUP_LOCATION', 'Primary'),
                'warehouse_name' => 'TOUCHSTAINS Central Warehouse',
                'warehouse_address' => 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion',
                'warehouse_city' => 'Mumbai',
                'warehouse_state' => 'Maharashtra',
                'warehouse_pincode' => '400022',
                'contact_person' => 'Naushad Ali',
                'contact_number' => '+91 7679416294',
                'gst_number' => '27AAACT0000A1Z5',
                'default_weight' => 0.50,
                'default_length' => 15.00,
                'default_breadth' => 10.00,
                'default_height' => 10.00,
                'auto_shipment_on_confirm' => false,
                'auto_courier_selection' => true,
                'auto_generate_awb' => true,
                'auto_schedule_pickup' => false,
                'webhook_token' => bin2hex(random_bytes(16)),
                'connection_status' => 'not_configured',
                'is_active' => true,
            ]);
        }
        return $setting;
    }

    public function isConnected(): bool
    {
        return $this->connection_status === 'connected';
    }

    public function isProduction(): bool
    {
        return strtolower($this->environment) === 'production';
    }
}
