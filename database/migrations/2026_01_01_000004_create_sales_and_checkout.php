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
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('image')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('qr_code')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('type_id');
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('carts', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('type_id');
            $table->timestamps();
        });

        Schema::create('cart_items', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('vendor_inventory_id');
            $table->integer('quantity');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('type_id');
            $table->timestamps();
        });

        Schema::create('orders', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('currency_id');
            $table->decimal('subtotal', 16, 2);
            $table->decimal('tax', 16, 2)->default(0);
            $table->decimal('shipping_fee', 16, 2)->default(0);
            $table->decimal('total', 16, 2);
            $table->text('notes')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('billing_address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('vendor_inventory_id');
            $table->decimal('price', 16, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 16, 2);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->decimal('commission_amount', 16, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payments', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('payment_methods');
    }
};
