<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Merchant Bank Accounts Table
        if (!Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('account_holder_name')->default('ZOYA ENTERPRISES');
                $table->string('bank_name')->default('AU Small Finance Bank');
                $table->string('account_number')->default('2502266564968702');
                $table->string('ifsc_code')->default('AUBL0002665');
                $table->string('account_type')->default('Current Account - Basic');
                $table->string('branch_name')->default('Sion East');
                $table->text('branch_address')->default('Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022, India.');
                $table->boolean('is_active')->default(true);
                $table->text('instructions')->nullable();
                $table->timestamps();
            });

            // Seed default AU Small Finance Bank account
            DB::table('bank_accounts')->insert([
                'account_holder_name' => 'ZOYA ENTERPRISES',
                'bank_name' => 'AU Small Finance Bank',
                'account_number' => '2502266564968702',
                'ifsc_code' => 'AUBL0002665',
                'account_type' => 'Current Account - Basic',
                'branch_name' => 'Sion East',
                'branch_address' => 'Shop No. 3 & 4, Tower B, Braumonte, Road No. 28-A, Sion Koliwada, Sion, Mumbai, Maharashtra - 400022, India.',
                'is_active' => 1,
                'instructions' => 'Please transfer the exact order amount via NEFT, IMPS or RTGS to our official AU Small Finance Bank account. After completing the transfer, enter your UTR / Transaction Reference number and upload an optional payment screenshot below. Our verification team will confirm your order promptly.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Customer Bank Transfer Payment Submissions Table
        if (!Schema::hasTable('bank_transfers')) {
            Schema::create('bank_transfers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->decimal('amount', 10, 2);
                $table->string('transaction_date')->nullable();
                $table->string('utr_number')->nullable()->index();
                $table->string('screenshot_path')->nullable();
                $table->enum('status', [
                    'awaiting_payment',
                    'payment_submitted',
                    'verification_pending',
                    'payment_confirmed',
                    'payment_rejected'
                ])->default('awaiting_payment')->index();
                $table->text('admin_notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Payment Verification Audit Logs Table
        if (!Schema::hasTable('bank_transfer_logs')) {
            Schema::create('bank_transfer_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bank_transfer_id')->index();
                $table->unsignedBigInteger('order_id')->index();
                $table->string('action'); // created, submitted, verified, approved, rejected, note_added
                $table->string('previous_status')->nullable();
                $table->string('new_status')->nullable();
                $table->unsignedBigInteger('performed_by')->nullable();
                $table->string('actor_type')->default('customer'); // customer, admin, system
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transfer_logs');
        Schema::dropIfExists('bank_transfers');
        Schema::dropIfExists('bank_accounts');
    }
};
