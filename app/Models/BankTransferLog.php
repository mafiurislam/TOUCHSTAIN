<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransferLog extends Model
{
    protected $guarded = [];

    public function bankTransfer()
    {
        return $this->belongsTo(BankTransfer::class, 'bank_transfer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
