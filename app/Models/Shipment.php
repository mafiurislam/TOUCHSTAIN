<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'shipping_charges' => 'float',
        'cod_amount' => 'float',
        'package_weight' => 'float',
        'package_dimensions' => 'array',
        'api_response' => 'array',
        'etd_date' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'pickup_scheduled_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function trackingEvents()
    {
        return $this->hasMany(ShipmentTrackingEvent::class)->orderBy('event_time', 'asc');
    }

    public function latestTrackingEvent()
    {
        return $this->hasOne(ShipmentTrackingEvent::class)->latestOfMany('event_time');
    }

    public function returns()
    {
        return $this->hasMany(ShipmentReturn::class);
    }

    public function canGenerateAwb(): bool
    {
        return empty($this->awb_number) && !in_array($this->status, ['cancelled', 'delivered', 'rto_delivered']);
    }

    public function canSchedulePickup(): bool
    {
        return !empty($this->awb_number) && $this->pickup_status !== 'scheduled' && $this->pickup_status !== 'picked_up' && !in_array($this->status, ['cancelled', 'delivered']);
    }

    public function canCancel(): bool
    {
        return !in_array($this->status, ['cancelled', 'delivered', 'rto_delivered', 'rto_initiated', 'out_for_delivery']);
    }

    public function canCreateReturn(): bool
    {
        return $this->status === 'delivered';
    }

    public function hasRemoteLabelUrl(): bool
    {
        return !empty($this->label_url)
            && (str_starts_with($this->label_url, 'http://') || str_starts_with($this->label_url, 'https://'))
            && !str_contains($this->label_url, 'admin/delivery/shipments');
    }

    public function getFormattedStatusAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'delivered' => '#059669',
            'in_transit', 'picked_up', 'out_for_delivery' => '#2563EB',
            'awb_assigned', 'pickup_scheduled' => '#01684B',
            'cancelled', 'lost' => '#DC2626',
            'ndr', 'delayed', 'undelivered' => '#D97706',
            'rto_initiated', 'rto_delivered' => '#7C3AED',
            default => '#4B5563',
        };
    }

    public function getStatusBgAttribute(): string
    {
        return match($this->status) {
            'delivered' => '#ECFDF5',
            'in_transit', 'picked_up', 'out_for_delivery' => '#EFF6FF',
            'awb_assigned', 'pickup_scheduled' => '#E6F4EA',
            'cancelled', 'lost' => '#FEF2F2',
            'ndr', 'delayed', 'undelivered' => '#FFFBEB',
            'rto_initiated', 'rto_delivered' => '#F5F3FF',
            default => '#F3F4F6',
        };
    }
}
