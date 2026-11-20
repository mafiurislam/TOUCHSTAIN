<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Banner extends Model
{
    protected $guarded = [];

    /**
     * Ensure media_type and video columns exist on banners table (zero-downtime for SQLite/Hostinger).
     */
    public static function ensureMediaColumns(): void
    {
        try {
            if (!Schema::hasColumn('banners', 'media_type') || !Schema::hasColumn('banners', 'video')) {
                Schema::table('banners', function (Blueprint $table) {
                    if (!Schema::hasColumn('banners', 'media_type')) {
                        $table->string('media_type', 20)->default('image');
                    }
                    if (!Schema::hasColumn('banners', 'video')) {
                        $table->string('video', 500)->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Banner::ensureMediaColumns failed: ' . $e->getMessage());
        }
    }

    public function getIsVideoAttribute(): bool
    {
        if (($this->media_type ?? 'image') === 'video') {
            return true;
        }
        $candidate = (string) ($this->video ?: $this->image ?: '');
        $ext = strtolower(pathinfo(parse_url($candidate, PHP_URL_PATH) ?: $candidate, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'webm', 'mov', 'm4v', 'ogg'], true);
    }

    public function getMediaUrlAttribute(): string
    {
        $file = $this->is_video
            ? trim((string) ($this->video ?: $this->image))
            : trim((string) $this->image);

        if ($file === '') {
            return asset('assets/image/place_holder.png');
        }
        if (str_starts_with($file, 'http://') || str_starts_with($file, 'https://')) {
            return $file;
        }
        if (str_starts_with($file, 'videos/') || str_starts_with($file, 'storage/')) {
            return asset(ltrim($file, '/'));
        }
        return asset('storage/banner/' . ltrim($file, '/'));
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('assets/image/place_holder.png');
        }
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }
        return asset('storage/banner/' . ltrim($this->image, '/'));
    }
}
