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
        Schema::create('vendor_inventories', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('inventory_status_id');
            $table->decimal('price', 16, 2);
            $table->decimal('stock_quantity', 16, 2)->default(0);
            $table->date('harvest_date')->nullable();
            $table->string('farm_location')->nullable();
            $table->string('province_of_origin')->nullable();
            $table->string('certification_type')->nullable();
            $table->unsignedBigInteger('packaging_type_id')->nullable();
            $table->integer('shelf_life_days')->nullable();
            $table->text('batch_images')->nullable();
            $table->unsignedBigInteger('currency_id');
            $table->timestamps();
            $table->softDeletes();
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
