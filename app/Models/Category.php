<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->where('status', 1);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id')->where('status', 1);
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('assets/image/place_holder.png');
        }
        return asset('storage/category/' . $this->image);
    }
}

