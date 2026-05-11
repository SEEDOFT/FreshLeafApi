<?php

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
        Schema::create('vendor_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->decimal('stock_quantity', 12, 2);
            $table->foreignId('unit_id')->constrained('units');
            $table->boolean('is_active')->default(true);
            $table->date('harvest_date')->nullable();
            $table->string('farm_location')->nullable();
            $table->json('batch_images')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_inventories');
    }
};
