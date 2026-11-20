<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the currently active merchant bank account.
     */
    public static function getActiveAccount()
    {
        return static::where('is_active', true)->first() ?: static::first();
    }
}
