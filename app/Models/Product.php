<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

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
    ];

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

