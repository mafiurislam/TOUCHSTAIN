<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getReviewerNameAttribute(): string
    {
        if (!empty($this->customer_name) && $this->customer_name !== 'Verified Shopper') {
            return $this->customer_name;
        }
        if ($this->user && !empty($this->user->name)) {
            return $this->user->name;
        }
        if (!empty($this->customer_name)) {
            return $this->customer_name;
        }
        return 'Verified Customer';
    }
}
