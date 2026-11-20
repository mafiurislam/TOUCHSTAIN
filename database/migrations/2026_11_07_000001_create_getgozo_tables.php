<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
                $table->decimal('wallet_balance', 10, 2)->default(120.00)->after('password');
                $table->integer('loyalty_points')->default(35)->after('wallet_balance');
                $table->string('referral_code')->default('GETGOZO732201')->after('loyalty_points');
                $table->string('role')->default('customer')->after('referral_code');
            }
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->integer('coverage')->default(2);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->default(0)->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('image')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->json('image')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('discount_type')->default('percent');
            $table->decimal('tax', 10, 2)->default(0);
            $table->string('tax_type')->default('percent');
            $table->string('unit')->default('pc');
            $table->decimal('capacity', 10, 2)->default(1);
            $table->integer('total_stock')->default(0);
            $table->boolean('daily_needs')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('popularity_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('maximum_order_quantity')->default(10);
            $table->decimal('weight', 10, 2)->default(0);
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('sub_category_id')->nullable()->index();
            $table->json('category_ids')->nullable();
            $table->json('variations')->nullable();
            $table->json('choice_options')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code')->unique();
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('discount_type')->default('amount');
            $table->decimal('min_purchase', 10, 2)->default(69);
            $table->decimal('max_discount', 10, 2)->default(100);
            $table->boolean('status')->default(true);
            $table->string('expire_date')->nullable();
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('address_type')->default('Home');
            $table->string('contact_person_name');
            $table->string('contact_person_number');
            $table->text('address');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->unsignedBigInteger('branch_id')->default(1);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->default(1);
            $table->decimal('order_amount', 10, 2);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('product_discount', 10, 2)->default(0);
            $table->decimal('coupon_discount_amount', 10, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->string('payment_method')->default('Cash on Delivery');
            $table->string('payment_status')->default('unpaid');
            $table->string('order_status')->default('confirmed');
            $table->string('order_type')->default('Home Delivery');
            $table->text('delivery_address');
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_number')->nullable();
            $table->string('time_slot')->nullable();
            $table->text('order_note')->nullable();
            $table->decimal('bring_change_amount', 10, 2)->nullable();
            $table->integer('earned_loyalty_points')->default(0);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('product_name');
            $table->string('product_image')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('old_price', 10, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('discount_on_product', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('session_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_name')->default('Verified Shopper');
            $table->integer('rating')->default(5);
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('sender')->default('customer');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletters');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('products');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('settings');
    }
};

