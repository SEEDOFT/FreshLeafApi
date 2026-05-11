<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_batches');
        Schema::dropIfExists('inventory_batch_statuses');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_order_statuses');
        Schema::dropIfExists('suppliers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No turning back for this version
    }
};
