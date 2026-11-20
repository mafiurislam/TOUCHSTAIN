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
        // 1. Delivery Partner Settings
        Schema::create('delivery_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('shiprocket'); // shiprocket
            $table->string('api_email')->nullable();
            $table->text('api_password')->nullable(); // encrypted
            $table->text('api_token')->nullable();
            $table->dateTime('token_expires_at')->nullable();
            $table->string('environment')->default('test'); // 'test' (sandbox) or 'production'
            
            // Warehouse & Pickup Location
            $table->string('pickup_location_name')->default('Primary');
            $table->string('warehouse_name')->default('TOUCHSTAINS Central Warehouse');
            $table->text('warehouse_address')->default('Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion');
            $table->string('warehouse_city')->default('Mumbai');
            $table->string('warehouse_state')->default('Maharashtra');
            $table->string('warehouse_pincode')->default('400022');
            $table->string('contact_person')->default('Naushad Ali');
            $table->string('contact_number')->default('+91 7679416294');
            $table->string('gst_number')->nullable();

            // Default Package Dimensions
            $table->decimal('default_weight', 8, 2)->default(0.50); // kg
            $table->decimal('default_length', 8, 2)->default(15.00); // cm
            $table->decimal('default_breadth', 8, 2)->default(10.00); // cm
            $table->decimal('default_height', 8, 2)->default(10.00); // cm

            // Automation Preferences
            $table->boolean('auto_shipment_on_confirm')->default(false);
            $table->boolean('auto_courier_selection')->default(true);
            $table->boolean('auto_generate_awb')->default(true);
            $table->boolean('auto_schedule_pickup')->default(false);

            // Webhook & Security
            $table->string('webhook_token')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('connection_status')->default('not_configured'); // 'connected', 'failed', 'not_configured'
            $table->dateTime('last_connected_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        // 2. Shipments Table
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('delivery_provider')->default('shiprocket');
            $table->string('shiprocket_order_id')->nullable()->index();
            $table->string('shiprocket_shipment_id')->nullable()->index();
            $table->string('awb_number')->nullable()->index();
            $table->unsignedBigInteger('courier_company_id')->nullable()->index();
            $table->string('courier_name')->nullable();
            
            // Pickup details
            $table->string('pickup_token_number')->nullable();
            $table->date('pickup_scheduled_date')->nullable();
            $table->string('pickup_status')->default('pending'); // 'pending', 'scheduled', 'picked_up'

            // Financials & Dimensions
            $table->decimal('shipping_charges', 10, 2)->default(0.00);
            $table->string('payment_method')->default('Prepaid'); // 'COD' or 'Prepaid'
            $table->decimal('cod_amount', 10, 2)->default(0.00);
            $table->decimal('package_weight', 8, 2)->default(0.50);
            $table->json('package_dimensions')->nullable();
            $table->string('destination_pincode')->nullable();

            // Status Lifecycle
            $table->string('status')->default('pending')->index(); 
            // 'pending', 'awb_assigned', 'pickup_scheduled', 'picked_up', 'in_transit', 
            // 'reached_hub', 'out_for_delivery', 'delivered', 'undelivered', 'delayed', 
            // 'ndr', 'rto_initiated', 'rto_delivered', 'cancelled', 'lost'
            $table->string('sub_status')->nullable();

            // Generated Documents
            $table->text('label_url')->nullable();
            $table->text('manifest_url')->nullable();
            $table->text('invoice_url')->nullable();
            $table->text('tracking_url')->nullable();

            // Dates & Audit
            $table->dateTime('etd_date')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('idempotency_key')->unique();
            $table->json('api_response')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        // 3. Live Shipment Tracking Events
        Schema::create('shipment_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id')->index();
            $table->string('status');
            $table->string('activity');
            $table->string('location')->nullable();
            $table->dateTime('event_time');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
        });

        // 4. Cached Courier Services
        Schema::create('courier_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('courier_company_id')->index();
            $table->string('name');
            $table->string('provider')->default('shiprocket');
            $table->decimal('rating', 3, 1)->default(4.5);
            $table->boolean('is_cod_supported')->default(true);
            $table->boolean('is_prepaid_supported')->default(true);
            $table->string('estimated_delivery_days')->nullable();
            $table->decimal('last_rate', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Shipment Returns & Reverse Pickup
        Schema::create('shipment_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id')->index();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('shiprocket_return_order_id')->nullable();
            $table->string('shiprocket_return_shipment_id')->nullable();
            $table->string('return_awb')->nullable()->index();
            $table->string('courier_name')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->default('initiated'); // 'initiated', 'pickup_scheduled', 'in_transit', 'rto_delivered', 'completed', 'cancelled'
            $table->text('pickup_address')->nullable();
            $table->json('api_response')->nullable();
            $table->timestamps();

            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        // 6. Delivery API Audit Logs
        Schema::create('delivery_api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('shiprocket');
            $table->string('endpoint');
            $table->string('method', 10);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->integer('status_code')->nullable();
            $table->boolean('is_success')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_api_logs');
        Schema::dropIfExists('shipment_returns');
        Schema::dropIfExists('courier_services');
        Schema::dropIfExists('shipment_tracking_events');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('delivery_settings');
    }
};
