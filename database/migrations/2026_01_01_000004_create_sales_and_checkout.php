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
        Schema::create('payment_methods', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_method_type_id');
            $table->unsignedBigInteger('payment_method_status_id');
            $table->string('label')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('card_number')->nullable();
            $table->unsignedTinyInteger('expiry_month')->nullable();
            $table->unsignedSmallInteger('expiry_year')->nullable();
            $table->string('cvv')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_zip_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wishlists', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('wishlist_status_id');
            $table->unsignedBigInteger('vendor_inventory_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('carts', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vendor_inventory_id');
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('cart_status_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', static function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('order_type_id');
            $table->unsignedBigInteger('order_status_id');
            $table->unsignedBigInteger('payment_status_id');
            $table->date('delivery_date');
            $table->string('delivery_slot');
            $table->decimal('subtotal', 16, 2);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('delivery_fee', 16, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->decimal('total_amount', 16, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('vendor_inventory_id');
            $table->string('product_name_snapshot');
            $table->string('unit_snapshot');
            $table->decimal('unit_price_snapshot', 16, 2);
            $table->decimal('quantity', 16, 2);
            $table->decimal('subtotal', 16, 2);
            $table->decimal('commission_amount', 16, 2)->default(0);
            $table->decimal('vendor_net_amount', 16, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payments', static function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('type_id');
            $table->decimal('amount', 16, 2);
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wallet_transactions', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('wallet_transaction_type_id');
            $table->unsignedBigInteger('wallet_transaction_status_id');
            $table->decimal('amount', 16, 2);
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->timestamps();
        });

        Schema::create('wallet_transaction_histories', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('wallet_transaction_id');
            $table->unsignedBigInteger('wallet_transaction_type_id');
            $table->unsignedBigInteger('wallet_transaction_status_id');
            $table->decimal('amount', 16, 2);
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('payment_methods');
    }
};
