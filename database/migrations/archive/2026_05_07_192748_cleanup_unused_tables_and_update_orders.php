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
        // 1. Update order_items
        Schema::table('order_items', static function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn(['product_id', 'product_variant_id']);

            $table->foreignId('vendor_inventory_id')->nullable()->constrained('vendor_inventories');
        });

        // 2. Drop obsolete tables
        Schema::dropIfExists('price_histories');
        Schema::dropIfExists('product_discount_histories');
        Schema::dropIfExists('product_discounts');
        Schema::dropIfExists('product_substitutions');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('inventory_movement_types');
        Schema::dropIfExists('behavior_event_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We do not define down methods for dropping these tables as they are obsolete
        // and re-creating them goes against the new architecture.
        Schema::table('order_items', static function (Blueprint $table) {
            $table->dropForeign(['vendor_inventory_id']);
            $table->dropColumn(['vendor_inventory_id']);

            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants');
        });
    }
};
