<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'priority')) {
                    $table->integer('priority')->default(0)->nullable()->after('position');
                }
            });

            // Backfill priority from position if available
            try {
                if (Schema::hasColumn('categories', 'position') && Schema::hasColumn('categories', 'priority')) {
                    DB::table('categories')
                        ->where(function ($q) {
                            $q->whereNull('priority')->orWhere('priority', 0);
                        })
                        ->update(['priority' => DB::raw('position')]);
                }
            } catch (\Throwable $e) {
                // Ignore backfill errors
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'priority')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('priority');
            });
        }
    }
};
