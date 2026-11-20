<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_videos');
    }
};
