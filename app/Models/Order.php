<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'order_amount' => 'float',
        'subtotal' => 'float',
        'product_discount' => 'float',
        'coupon_discount_amount' => 'float',
        'delivery_charge' => 'float',
        'delivery_address' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function bankTransfer()
    {
        return $this->hasOne(BankTransfer::class, 'order_id')->latestOfMany();
    }

    public function bankTransfers()
    {
        return $this->hasMany(BankTransfer::class, 'order_id')->orderByDesc('id');
    }

    public function bankTransferLogs()
    {
        return $this->hasMany(BankTransferLog::class, 'order_id')->orderByDesc('id');
    }

    /**
     * Delivery & Shiprocket relationships
     */
    public function shipment()
    {
        return $this->hasOne(Shipment::class, 'order_id')->latestOfMany();
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'order_id')->orderByDesc('id');
    }

    public function returns()
    {
        return $this->hasMany(ShipmentReturn::class, 'order_id')->orderByDesc('id');
    }

    public function isRazorpay(): bool
    {
        return in_array(strtolower($this->payment_method ?? ''), ['razorpay', 'online', 'online_payment']);
    }

    public function isUpi(): bool
    {
        return in_array(strtolower($this->payment_method ?? ''), ['upi', 'upi_qr', 'upi_payment']);
    }

    public function isBankTransfer(): bool
    {
        return false;
    }

    public function isOnlinePayment(): bool
    {
        return in_array(strtolower($this->payment_method), ['razorpay', 'online_payment', 'online', 'upi_qr', 'online_qr', 'upi']);
    }

    public function isCod(): bool
    {
        return in_array(strtolower($this->payment_method), ['cash_on_delivery', 'cod', 'cash on delivery']);
    }

    public function isPrepaid(): bool
    {
        return !$this->isCod();
    }

    public function requiresVerification(): bool
    {
        return $this->isBankTransfer();
    }

    /**
     * Calculate total weight of order in KG
     */
    public function calculateTotalWeight(): float
    {
        $weight = 0.0;
        foreach ($this->items as $item) {
            $prod = Product::find($item->product_id);
            $itemWeight = $prod && $prod->weight > 0 ? (float)$prod->weight : 0.5;
            if ($itemWeight > 10) {
                // Stored in grams (e.g. 200g, 500g, 1000g) -> convert to KG
                $itemWeight = $itemWeight / 1000;
            }
            $weight += $itemWeight * max(1, (int)$item->quantity);
        }
        return max(0.2, round($weight, 2));
    }

    /**
     * Extract recipient / destination pincode from order delivery address
     */
    public function getDeliveryPincode(): string
    {
        $addr = $this->delivery_address;
        if (is_array($addr)) {
            if (!empty($addr['pincode'])) return (string)$addr['pincode'];
            if (!empty($addr['postal_code'])) return (string)$addr['postal_code'];
            if (!empty($addr['zip'])) return (string)$addr['zip'];
            
            // Regex match 6 digit Indian pincode
            $str = ($addr['address'] ?? '') . ' ' . ($addr['street'] ?? '') . ' ' . ($addr['city'] ?? '');
            if (preg_match('/\b[1-9][0-9]{5}\b/', $str, $matches)) {
                return $matches[0];
            }
        } elseif (is_string($addr)) {
            if (preg_match('/\b[1-9][0-9]{5}\b/', $addr, $matches)) {
                return $matches[0];
            }
        }
        return '400074'; // Default Mumbai Chembur hub pincode fallback
    }

    public function getDeliveryCity(): string
    {
        $addr = $this->delivery_address;
        if (is_array($addr) && !empty($addr['city'])) {
            return (string)$addr['city'];
        }
        $full = is_array($addr) ? ($addr['address'] ?? '') : (string)$addr;
        foreach (['Mumbai', 'Delhi', 'Kolkata', 'Bangalore', 'Bengaluru', 'Chennai', 'Hyderabad', 'Pune', 'Ahmedabad', 'Surat', 'Jaipur', 'Lucknow', 'Kanpur', 'Nagpur', 'Indore', 'Thane', 'Bhopal', 'Visakhapatnam', 'Patna', 'Vadodara', 'Ghaziabad', 'Ludhiana', 'Agra', 'Nashik', 'Faridabad', 'Meerut', 'Rajkot', 'Varanasi', 'Srinagar', 'Aurangabad', 'Dhanbad', 'Amritsar', 'Navi Mumbai', 'Allahabad', 'Howrah', 'Gwalior', 'Jabalpur', 'Coimbatore', 'Vijayawada', 'Jodhpur', 'Madurai', 'Raipur', 'Kota', 'Guwahati', 'Chandigarh', 'Solapur', 'Hubli', 'Noida', 'Gurgaon', 'Bhubaneswar', 'Dehradun', 'Kochi'] as $city) {
            if (stripos($full, $city) !== false) {
                return $city;
            }
        }
        return 'Mumbai';
    }

    public function getDeliveryState(): string
    {
        $addr = $this->delivery_address;
        if (is_array($addr) && !empty($addr['state'])) {
            return (string)$addr['state'];
        }
        $full = is_array($addr) ? ($addr['address'] ?? '') : (string)$addr;
        foreach (['Maharashtra', 'West Bengal', 'Delhi', 'Karnataka', 'Tamil Nadu', 'Gujarat', 'Uttar Pradesh', 'Telangana', 'Andhra Pradesh', 'Kerala', 'Rajasthan', 'Madhya Pradesh', 'Punjab', 'Haryana', 'Bihar', 'Odisha', 'Assam', 'Jharkhand', 'Chhattisgarh', 'Uttarakhand', 'Goa', 'Himachal Pradesh', 'Tripura', 'Meghalaya', 'Manipur', 'Nagaland', 'Mizoram', 'Sikkim', 'Arunachal Pradesh'] as $st) {
            if (stripos($full, $st) !== false) {
                return $st;
            }
        }
        return 'Maharashtra';
    }

    /**
     * Clean delivery address string
     */
    public function getDeliveryAddressFormatted(): string
    {
        $addr = $this->delivery_address;
        if (is_array($addr)) {
            return $addr['address'] ?? ($addr['street'] ?? 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022');
        }
        return (string)($addr ?: 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022');
    }

    public function getCustomerName(): string
    {
        $addr = $this->delivery_address;
        if (is_array($addr) && !empty($addr['contact_person_name'])) {
            return $addr['contact_person_name'];
        }
        if ($this->user) {
            return trim($this->user->f_name . ' ' . $this->user->l_name) ?: ($this->user->name ?: 'TOUCHSTAIN Customer');
        }
        return 'TOUCHSTAIN Customer';
    }

    public function getCustomerPhone(): string
    {
        $addr = $this->delivery_address;
        if (is_array($addr) && !empty($addr['contact_person_number'])) {
            return $addr['contact_person_number'];
        }
        return $this->user->phone ?? '+91 7679416294';
    }

    /**
     * Generate a unique, secure QR token for smart product & order tracking
     */
    public function getQrToken(): string
    {
        $hash = substr(hash('sha256', 'TOUCHSTAINS_QR_' . $this->id . '_' . ($this->created_at ? $this->created_at->timestamp : '1')), 0, 8);
        return 'ORD-' . $this->id . '-' . strtoupper($hash);
    }
}
