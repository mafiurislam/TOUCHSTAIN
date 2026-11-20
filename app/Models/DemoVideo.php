<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DemoVideo extends Model
{
    protected $table = 'demo_videos';

    protected $fillable = [
        'tag_label',
        'title',
        'subtitle',
        'video_path',
        'product_id',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'sort_order' => 'integer',
        'status' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getVideoUrlAttribute(): string
    {
        $path = trim((string) $this->video_path);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    }

    public function getProductUrlAttribute(): string
    {
        if (!empty($this->product_id)) {
            return url('/product/' . $this->product_id);
        }
        return url('/products');
    }

    /**
     * Auto-create the demo_videos table if missing (zero-downtime SQLite/shared hosting)
     * and seed the 6 default live action demo videos once.
     */
    public static function ensureTableAndSeed(): void
    {
        try {
            $justCreated = false;
            if (!Schema::hasTable('demo_videos')) {
                Schema::create('demo_videos', function (Blueprint $table) {
                    $table->id();
                    $table->string('tag_label', 80)->nullable();
                    $table->string('title', 200);
                    $table->string('subtitle', 220)->nullable();
                    $table->string('video_path', 500);
                    $table->unsignedBigInteger('product_id')->nullable();
                    $table->integer('sort_order')->default(0);
                    $table->boolean('status')->default(true);
                    $table->timestamps();
                });
                $justCreated = true;
            }

            $alreadySeeded = false;
            try {
                if (Schema::hasTable('settings')) {
                    $alreadySeeded = Setting::where('key', 'demo_videos_seeded')->exists();
                }
            } catch (\Throwable $e) {
                $alreadySeeded = false;
            }

            if (($justCreated || !$alreadySeeded) && self::count() === 0) {
                $defaults = [
                    [
                        'tag_label' => 'Ink Demo',
                        'title' => 'Ballpen & Ink Stain Removal',
                        'subtitle' => 'Instant Results • Live Proof',
                        'video_path' => 'videos/touchstains-demo-1.mp4',
                        'product_id' => 1,
                        'sort_order' => 1,
                        'status' => true,
                    ],
                    [
                        'tag_label' => 'Rust Demo',
                        'title' => 'Rust & Iron Stain Removal',
                        'subtitle' => 'Vanishes in 2 Drops Magic',
                        'video_path' => 'videos/touchstains-demo-2.mp4',
                        'product_id' => 2,
                        'sort_order' => 2,
                        'status' => true,
                    ],
                    [
                        'tag_label' => 'Tough Stains',
                        'title' => 'Turmeric, Oil & Curry Stains',
                        'subtitle' => '100% Effective Magic Formula',
                        'video_path' => 'videos/touchstains-demo-3.mp4',
                        'product_id' => 10,
                        'sort_order' => 3,
                        'status' => true,
                    ],
                    [
                        'tag_label' => 'Uniform Care',
                        'title' => 'School Uniforms & White Clothes',
                        'subtitle' => 'Bright & Stain-Free Clothes',
                        'video_path' => 'videos/touchstains-demo-4.mp4',
                        'product_id' => 5,
                        'sort_order' => 4,
                        'status' => true,
                    ],
                    [
                        'tag_label' => 'Spray Action',
                        'title' => 'Instant Spray Live Action',
                        'subtitle' => 'Large Areas & Collar Cleaning',
                        'video_path' => 'videos/touchstains-demo-5.mp4',
                        'product_id' => 7,
                        'sort_order' => 5,
                        'status' => true,
                    ],
                    [
                        'tag_label' => 'Enzyme Clean',
                        'title' => 'Active Enzyme Wash Test',
                        'subtitle' => '100% Safe on Colored Fabrics',
                        'video_path' => 'videos/touchstains-demo-6.mp4',
                        'product_id' => 8,
                        'sort_order' => 6,
                        'status' => true,
                    ],
                ];

                foreach ($defaults as $row) {
                    self::create($row);
                }
            }

            if (!$alreadySeeded) {
                try {
                    if (Schema::hasTable('settings')) {
                        Setting::updateOrCreate(
                            ['key' => 'demo_videos_seeded'],
                            ['value' => '1']
                        );
                    }
                } catch (\Throwable $e) {
                    // Ignore if settings table has different schema
                }
            }
        } catch (\Throwable $e) {
            Log::warning('DemoVideo::ensureTableAndSeed failed: ' . $e->getMessage());
        }
    }
}
