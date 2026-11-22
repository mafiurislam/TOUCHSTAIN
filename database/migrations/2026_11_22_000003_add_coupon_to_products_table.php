<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'coupon_id')) {
                    $table->unsignedBigInteger('coupon_id')->nullable()->after('status');
                }
                if (!Schema::hasColumn('products', 'coupon_code')) {
                    $table->string('coupon_code', 50)->nullable()->after('coupon_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'coupon_code')) {
                    $table->dropColumn('coupon_code');
                }
                if (Schema::hasColumn('products', 'coupon_id')) {
                    $table->dropColumn('coupon_id');
                }
            });
        }
    }
};
