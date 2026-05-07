<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create Status and Type Tables
        $statusTypeTables = [
            'user_cart_statuses', 'user_cart_types',
            'user_cart_item_statuses', 'user_cart_item_types',
            'user_wishlist_statuses', 'user_wishlist_types',
            'user_wishlist_item_statuses', 'user_wishlist_item_types',
        ];

        foreach ($statusTypeTables as $tableName) {
            Schema::create($tableName, static function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        // 2. Carts Table Updates
        // First drop foreign key on cart_status_id before dropping column to avoid SQLite issues
        Schema::table('carts', static function (Blueprint $table) {
            $table->dropForeign(['cart_status_id']);
            $table->dropColumn('cart_status_id');
        });

        Schema::rename('carts', 'user_carts');

        Schema::table('user_carts', static function (Blueprint $table) {
            $table->foreignId('user_cart_status_id')->nullable()->constrained('user_cart_statuses');
            $table->foreignId('user_cart_type_id')->nullable()->constrained('user_cart_types');
        });

        // 3. Cart Items Table Updates
        Schema::table('cart_items', static function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn(['product_id', 'product_variant_id']);
        });

        Schema::rename('cart_items', 'user_cart_items');

        Schema::table('user_cart_items', static function (Blueprint $table) {
            $table->foreignId('vendor_inventory_id')->nullable()->constrained('vendor_inventories');
            $table->foreignId('user_cart_item_status_id')->nullable()->constrained('user_cart_item_statuses');
            $table->foreignId('user_cart_item_type_id')->nullable()->constrained('user_cart_item_types');
        });

        // 4. Create Wishlists Table
        Schema::create('user_wishlists', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_wishlist_status_id')->constrained('user_wishlist_statuses');
            $table->foreignId('user_wishlist_type_id')->constrained('user_wishlist_types');
            $table->timestamps();
        });

        // 5. Create Wishlist Items Table
        Schema::create('user_wishlist_items', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_wishlist_id')->constrained('user_wishlists')->cascadeOnDelete();
            $table->foreignId('vendor_inventory_id')->constrained('vendor_inventories')->cascadeOnDelete();
            $table->foreignId('user_wishlist_item_status_id')->constrained('user_wishlist_item_statuses');
            $table->foreignId('user_wishlist_item_type_id')->constrained('user_wishlist_item_types');
            $table->timestamps();
        });

        // 6. Cleanup old cart_statuses if no longer needed
        Schema::dropIfExists('cart_statuses');
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wishlist_items');
        Schema::dropIfExists('user_wishlists');

        Schema::rename('user_cart_items', 'cart_items');
        Schema::table('cart_items', static function (Blueprint $table) {
            $table->dropForeign(['vendor_inventory_id']);
            $table->dropForeign(['user_cart_item_status_id']);
            $table->dropForeign(['user_cart_item_type_id']);
            $table->dropColumn(['vendor_inventory_id', 'user_cart_item_status_id', 'user_cart_item_type_id']);

            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants');
        });

        Schema::rename('user_carts', 'carts');
        Schema::create('cart_statuses', static function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('carts', static function (Blueprint $table) {
            $table->dropForeign(['user_cart_status_id']);
            $table->dropForeign(['user_cart_type_id']);
            $table->dropColumn(['user_cart_status_id', 'user_cart_type_id']);

            $table->foreignId('cart_status_id')->nullable()->constrained('cart_statuses');
        });

        $statusTypeTables = [
            'user_cart_statuses', 'user_cart_types',
            'user_cart_item_statuses', 'user_cart_item_types',
            'user_wishlist_statuses', 'user_wishlist_types',
            'user_wishlist_item_statuses', 'user_wishlist_item_types',
        ];

        foreach (array_reverse($statusTypeTables) as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
