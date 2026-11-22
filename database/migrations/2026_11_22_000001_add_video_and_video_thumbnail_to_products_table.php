<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'video')) {
                $table->string('video', 500)->nullable()->after('image');
            }
            if (!Schema::hasColumn('products', 'video_thumbnail')) {
                $table->string('video_thumbnail', 500)->nullable()->after('video');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'video_thumbnail')) {
                $table->dropColumn('video_thumbnail');
            }
            if (Schema::hasColumn('products', 'video')) {
                $table->dropColumn('video');
            }
        });
    }
};
