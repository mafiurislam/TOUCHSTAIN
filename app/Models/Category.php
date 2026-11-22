<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Category extends Model
{
    protected $guarded = [];

    /**
     * Ensure required columns exist on categories table (zero-downtime on SQLite/Hostinger).
     */
    public static function ensureColumns(): void
    {
        try {
            if (!Schema::hasTable('categories')) {
                return;
            }

            $needsPriority = !Schema::hasColumn('categories', 'priority');
            $needsPosition = !Schema::hasColumn('categories', 'position');
            $needsSlug = !Schema::hasColumn('categories', 'slug');

            if ($needsPriority || $needsPosition || $needsSlug) {
                Schema::table('categories', function (Blueprint $table) use ($needsPriority, $needsPosition, $needsSlug) {
                    if ($needsPriority) {
                        $table->integer('priority')->default(0)->nullable();
                    }
                    if ($needsPosition) {
                        $table->integer('position')->default(0)->nullable();
                    }
                    if ($needsSlug) {
                        $table->string('slug')->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Category::ensureColumns error: ' . $e->getMessage());
        }
    }

    protected static function booted()
    {
        static::ensureColumns();
    }

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

    public function getPriorityAttribute()
    {
        return $this->attributes['priority'] ?? $this->attributes['position'] ?? 0;
    }

    public function setPriorityAttribute($value)
    {
        $this->attributes['priority'] = (int)$value;
        $this->attributes['position'] = (int)$value;
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('assets/image/place_holder.png');
        }
        if (file_exists(public_path('storage/category/' . $this->image))) {
            return asset('storage/category/' . $this->image);
        }
        if (file_exists(public_path('assets/image/' . $this->image))) {
            return asset('assets/image/' . $this->image);
        }
        return asset('assets/image/place_holder.png');
    }
}
