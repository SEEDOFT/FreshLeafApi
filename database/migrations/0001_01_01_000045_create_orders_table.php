<?php

declare(strict_types=1);

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
        Schema::create('orders', static function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('order_type_id');
            $table->unsignedBigInteger('order_status_id');
            $table->unsignedBigInteger('payment_status_id');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('commission_fee_history_id')->nullable();
            $table->unsignedBigInteger('exchange_rate_history_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->datetime('place_order_date')->nullable();
            $table->datetime('order_pending_date')->nullable();
            $table->datetime('order_confirmed_date')->nullable();
            $table->datetime('order_preparing_date')->nullable();
            $table->datetime('order_out_for_delivery_date')->nullable();
            $table->datetime('order_delivered_date')->nullable();
            $table->datetime('order_cancelled_date')->nullable();
            $table->datetime('order_awaiting_payment_date')->nullable();
            $table->date('delivery_date');
            $table->string('delivery_slot');
            $table->decimal('subtotal', 16, 2);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('delivery_fee', 16, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->decimal('total_amount', 16, 2);
            $table->string('preparation_proof_photo')->nullable();
            $table->string('delivery_company_name')->nullable();
            $table->text('delivery_tracking_info')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_vendor_paid')->default(false);
            $table->unsignedBigInteger('vendor_payout_transaction_id')->nullable();
            $table->string('delivery_proof_photo')->nullable();
            $table->datetime('consumer_confirmed_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
