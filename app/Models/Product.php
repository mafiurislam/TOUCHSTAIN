<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $appends = [
        'first_image',
        'image_url',
        'average_rating',
        'reviews_count',
        'coupon_title',
        'coupon_discount_label',
    ];

    protected $casts = [
        'image' => 'array',
        'category_ids' => 'array',
        'variations' => 'array',
        'choice_options' => 'array',
        'price' => 'float',
        'discount' => 'float',
        'capacity' => 'float',
        'total_stock' => 'integer',
        'daily_needs' => 'boolean',
        'is_featured' => 'boolean',
        'coupon_id' => 'integer',
    ];

    /**
     * Ensure coupon columns exist on products table (zero-downtime).
     */
    public static function ensureCouponColumns(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('products')) return;
            $needsId = !\Illuminate\Support\Facades\Schema::hasColumn('products', 'coupon_id');
            $needsCode = !\Illuminate\Support\Facades\Schema::hasColumn('products', 'coupon_code');
            if ($needsId || $needsCode) {
                \Illuminate\Support\Facades\Schema::table('products', function (\Illuminate\Database\Schema\Blueprint $table) use ($needsId, $needsCode) {
                    if ($needsId) {
                        $table->unsignedBigInteger('coupon_id')->nullable()->after('status');
                    }
                    if ($needsCode) {
                        $table->string('coupon_code', 50)->nullable()->after('coupon_id');
                    }
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Product::ensureCouponColumns failed: ' . $e->getMessage());
        }
    }

    protected static function booted()
    {
        static::ensureCouponColumns();
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function getAssignedCouponAttribute(): ?Coupon
    {
        if ($this->relationLoaded('coupon') && $this->coupon) {
            return $this->coupon;
        }
        if ($this->coupon_id) {
            $coupon = $this->coupon;
            if ($coupon) return $coupon;
        }
        if (!empty($this->coupon_code)) {
            return Coupon::where('code', $this->coupon_code)->first();
        }
        return null;
    }

    public function getCouponTitleAttribute(): ?string
    {
        $c = $this->assigned_coupon;
        if ($c && !empty($c->title)) {
            return $c->title;
        }
        if (!empty($this->coupon_code)) {
            return 'Special Offer';
        }
        return null;
    }

    public function getCouponDiscountLabelAttribute(): ?string
    {
        $c = $this->assigned_coupon;
        if ($c && (float)$c->discount > 0) {
            $val = ((int)$c->discount == $c->discount) ? (int)$c->discount : $c->discount;
            return ($c->discount_type === 'percent') ? ($val . '% OFF') : ('₹' . $val . ' OFF');
        }
        if (!empty($this->coupon_code)) {
            return 'OFFER';
        }
        return null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getImagesListAttribute(): array
    {
        $imgs = $this->image;
        if (is_string($imgs)) {
            $decoded = json_decode($imgs, true);
            $imgs = is_array($decoded) ? $decoded : (!empty($imgs) ? [$imgs] : []);
        }
        if (!is_array($imgs)) {
            return [];
        }
        return array_values(array_filter($imgs, fn($img) => !empty($img) && $img !== 'place_holder.png'));
    }

    public static function getProductImageUrl(?string $img): string
    {
        if (!$img || $img === 'place_holder.png') {
            return asset('assets/image/placeholder.png');
        }
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            return $img;
        }
        return asset('storage/product/' . ltrim($img, '/'));
    }

    public function getFirstImageAttribute(): string
    {
        $imgs = $this->image;
        if (is_array($imgs) && count($imgs) > 0) {
            return $imgs[0];
        }
        if (is_string($imgs) && !empty($imgs)) {
            return $imgs;
        }
        return 'place_holder.png';
    }

    public function getImageUrlAttribute(): string
    {
        $first = $this->first_image;
        if ($first === 'place_holder.png') {
            return asset('assets/image/place_holder.png');
        }
        return asset('storage/product/' . $first);
    }

    public function getVideoUrlAttribute(): ?string
    {
        if (!$this->video) {
            return null;
        }
        if (str_starts_with($this->video, 'http://') || str_starts_with($this->video, 'https://')) {
            return $this->video;
        }
        return asset('storage/product/videos/' . ltrim($this->video, '/'));
    }

    public function getVideoThumbnailUrlAttribute(): ?string
    {
        if ($this->video_thumbnail) {
            if (str_starts_with($this->video_thumbnail, 'http://') || str_starts_with($this->video_thumbnail, 'https://')) {
                return $this->video_thumbnail;
            }
            return asset('storage/product/' . ltrim($this->video_thumbnail, '/'));
        }
        return $this->image_url;
    }

    public function getAverageRatingAttribute(): float
    {
        $avg = $this->reviews()->avg('rating');
        return $avg ? round((float)$avg, 1) : 4.8;
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function getSellingPriceAttribute(): float
    {
        return round((float) $this->price, 2);
    }

    public function getFinalPriceAttribute(): float
    {
        return round((float) $this->price, 2);
    }

    public function getDiscountLabelAttribute(): string
    {
        return '';
    }

    public function getUnitLabelAttribute(): string
    {
        if ($this->capacity && $this->unit) {
            $cap = ((int)$this->capacity == $this->capacity) ? (int)$this->capacity : $this->capacity;
            return $cap . ' ' . $this->unit;
        }
        return $this->unit ?: '1 pc';
    }
}

