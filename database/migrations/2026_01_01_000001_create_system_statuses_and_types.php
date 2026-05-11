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
        // User & Auth
        Schema::create('user_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('user_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        // Products & Categories
        Schema::create('product_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('product_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('product_category_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('units', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name_en');
            $table->string('name_km');
            $table->string('symbol');
            $table->decimal('conversion_to_base', 16, 8)->default(1);
            $table->timestamps();
        });

        // Orders & Payments
        Schema::create('order_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->integer('sort_order')->default(0);
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('order_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('payment_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('payment_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('payment_method_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('payment_method_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        // Finance
        Schema::create('currencies', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->string('symbol');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wallet_transaction_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('wallet_transaction_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('payout_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('payout_methods', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        // Inventory
        Schema::create('vendor_inventory_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        // Notifications
        Schema::create('notification_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('notification_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        // Cart & Wishlist
        Schema::create('user_cart_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('user_cart_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('user_cart_item_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('user_cart_item_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('user_wishlist_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('user_wishlist_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('user_wishlist_item_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });

        Schema::create('user_wishlist_item_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_km');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_wishlist_item_types');
        Schema::dropIfExists('user_wishlist_item_statuses');
        Schema::dropIfExists('user_wishlist_types');
        Schema::dropIfExists('user_wishlist_statuses');
        Schema::dropIfExists('user_wishlist_item_types');
        Schema::dropIfExists('user_wishlist_item_statuses');
        Schema::dropIfExists('user_wishlist_types');
        Schema::dropIfExists('user_wishlist_statuses');
        Schema::dropIfExists('user_cart_item_types');
        Schema::dropIfExists('user_cart_item_statuses');
        Schema::dropIfExists('user_cart_types');
        Schema::dropIfExists('user_cart_statuses');
        Schema::dropIfExists('notification_statuses');
        Schema::dropIfExists('notification_types');
        Schema::dropIfExists('vendor_inventory_statuses');
        Schema::dropIfExists('payout_methods');
        Schema::dropIfExists('payout_statuses');
        Schema::dropIfExists('wallet_transaction_types');
        Schema::dropIfExists('wallet_transaction_statuses');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('payment_method_types');
        Schema::dropIfExists('payment_method_statuses');
        Schema::dropIfExists('payment_types');
        Schema::dropIfExists('payment_statuses');
        Schema::dropIfExists('order_types');
        Schema::dropIfExists('order_statuses');
        Schema::dropIfExists('units');
        Schema::dropIfExists('product_category_statuses');
        Schema::dropIfExists('product_types');
        Schema::dropIfExists('product_statuses');
        Schema::dropIfExists('user_types');
        Schema::dropIfExists('user_statuses');
    }
};
