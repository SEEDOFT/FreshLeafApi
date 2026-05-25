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
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
