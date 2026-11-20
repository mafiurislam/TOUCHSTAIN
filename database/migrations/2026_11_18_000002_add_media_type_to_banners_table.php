<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'media_type')) {
                $table->string('media_type', 20)->default('image');
            }
            if (!Schema::hasColumn('banners', 'video')) {
                $table->string('video', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'video')) {
                $table->dropColumn('video');
            }
            if (Schema::hasColumn('banners', 'media_type')) {
                $table->dropColumn('media_type');
            }
        });
    }
};
