<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentReturn extends Model
{
    protected $guarded = [];

    protected $casts = [
        'api_response' => 'array',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
