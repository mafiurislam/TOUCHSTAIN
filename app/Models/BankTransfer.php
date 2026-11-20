<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransfer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'float',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function logs()
    {
        return $this->hasMany(BankTransferLog::class, 'bank_transfer_id')->orderByDesc('id');
    }

    /**
     * Human-friendly status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'awaiting_payment' => 'Awaiting Payment',
            'payment_submitted' => 'Payment Submitted',
            'verification_pending' => 'Verification Pending',
            'payment_confirmed' => 'Payment Confirmed',
            'payment_rejected' => 'Payment Rejected',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Bootstrap/Tailwind badge color class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'awaiting_payment' => 'badge-warning text-dark',
            'payment_submitted' => 'badge-info text-white',
            'verification_pending' => 'badge-primary text-white',
            'payment_confirmed' => 'badge-success text-white',
            'payment_rejected' => 'badge-danger text-white',
            default => 'badge-secondary',
        };
    }
}
