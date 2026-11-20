<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierService extends Model
{
    protected $guarded = [];

    protected $casts = [
        'rating' => 'float',
        'last_rate' => 'float',
        'is_cod_supported' => 'boolean',
        'is_prepaid_supported' => 'boolean',
        'is_active' => 'boolean',
    ];
}
