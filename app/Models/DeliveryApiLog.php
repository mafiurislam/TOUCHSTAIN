<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryApiLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'is_success' => 'boolean',
    ];
}
