<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'wallet_balance',
        'loyalty_points',
        'referral_code',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'float',
            'loyalty_points' => 'integer',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function getFNameAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        $parts = preg_split('/\s+/', trim((string) ($this->attributes['name'] ?? '')), 2);
        return $parts[0] ?? '';
    }

    public function getLNameAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        $parts = preg_split('/\s+/', trim((string) ($this->attributes['name'] ?? '')), 2);
        return $parts[1] ?? '';
    }
}
