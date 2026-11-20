<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend Users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active'); // active, blocked
            }
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false);
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip')->nullable();
            }
            if (!Schema::hasColumn('users', 'permissions')) {
                $table->text('permissions')->nullable();
            }
        });

        // 2. Extend Products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'mrp')) {
                $table->decimal('mrp', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable();
            }
            if (!Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable();
            }
            if (!Schema::hasColumn('products', 'brand_id')) {
                $table->unsignedBigInteger('brand_id')->nullable();
            }
            if (!Schema::hasColumn('products', 'is_best_seller')) {
                $table->boolean('is_best_seller')->default(false);
            }
            if (!Schema::hasColumn('products', 'is_new_arrival')) {
                $table->boolean('is_new_arrival')->default(false);
            }
            if (!Schema::hasColumn('products', 'slug')) {
                $table->string('slug')->nullable();
            }
            if (!Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable();
            }
            if (!Schema::hasColumn('products', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }
            if (!Schema::hasColumn('products', 'specifications')) {
                $table->text('specifications')->nullable();
            }
            if (!Schema::hasColumn('products', 'bulk_min_qty')) {
                $table->integer('bulk_min_qty')->nullable()->default(5);
            }
            if (!Schema::hasColumn('products', 'bulk_price')) {
                $table->decimal('bulk_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'special_price')) {
                $table->decimal('special_price', 10, 2)->nullable();
            }
        });

        // 3. Brands table
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('image')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // 4. Inventory Logs table
        if (!Schema::hasTable('inventory_logs')) {
            Schema::create('inventory_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('type'); // stock_in, stock_out, order_deduct, order_restore, adjustment
                $table->integer('quantity');
                $table->integer('current_stock')->default(0);
                $table->string('notes')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }

        // 5. CMS Pages table
        if (!Schema::hasTable('cms_pages')) {
            Schema::create('cms_pages', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('title');
                $table->longText('content')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // 6. Audit Logs table
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->string('module');
                $table->string('action');
                $table->text('details')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('brands');
    }
};
