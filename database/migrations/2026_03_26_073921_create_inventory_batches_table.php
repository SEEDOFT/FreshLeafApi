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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('inventory_batch_status_id')->constrained('inventory_batch_statuses')->restrictOnDelete();
            $table->string('batch_code')->unique()->index();
            $table->decimal('received_qty', 12, 4);
            $table->decimal('reserved_qty', 12, 4)->default(0);
            $table->decimal('sold_qty', 12, 4)->default(0);
            $table->decimal('damaged_qty', 12, 4)->default(0);
            $table->decimal('expired_qty', 12, 4)->default(0);
            $table->decimal('cost_per_unit', 12, 2);
            $table->date('expiry_date')->nullable();
            $table->timestamp('received_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
