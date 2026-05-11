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
            $table->string('name');
            $table->string('code')->unique();
            $table->string('image')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('qr_code')->nullable();
            $table->foreignId('status_id')->constrained('payment_method_statuses');
            $table->foreignId('type_id')->constrained('payment_method_types');
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('carts', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('user_cart_statuses');
            $table->foreignId('type_id')->constrained('user_cart_types');
            $table->timestamps();
        });

        Schema::create('cart_items', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_inventory_id')->constrained('vendor_inventories');
            $table->integer('quantity');
            $table->foreignId('status_id')->constrained('user_cart_item_statuses');
            $table->foreignId('type_id')->constrained('user_cart_item_types');
            $table->timestamps();
        });

        Schema::create('orders', static function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('status_id')->constrained('order_statuses');
            $table->foreignId('type_id')->constrained('order_types');
            $table->foreignId('currency_id')->constrained();
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
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_inventory_id')->constrained('vendor_inventories');
            $table->decimal('price', 16, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 16, 2);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->decimal('commission_amount', 16, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payments', static function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('status_id')->constrained('payment_statuses');
            $table->foreignId('type_id')->constrained('payment_types');
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
