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
        Schema::create('product_categories', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name_en');
            $table->string('name_km');
            $table->string('slug')->unique();
            $table->text('description_en')->nullable();
            $table->text('description_km')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedBigInteger('product_category_status_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_category_id');
            $table->unsignedBigInteger('product_type_id');
            $table->unsignedBigInteger('default_unit_id');
            $table->unsignedBigInteger('product_status_id');
            $table->string('name_en');
            $table->string('name_km');
            $table->string('slug')->unique();
            $table->text('description_en')->nullable();
            $table->text('description_km')->nullable();
            $table->string('image_url')->nullable();
            $table->text('nutrition_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

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
            $table->string('packaging_type')->nullable();
            $table->integer('shelf_life_days')->nullable();
            $table->text('batch_images')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_adjustments', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_inventory_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('quantity_change', 16, 2);
            $table->string('type');
            $table->string('reason');
            $table->string('proof_image_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_discounts', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->decimal('discount_value', 16, 2);
            $table->string('discount_type');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_discounts');
        Schema::dropIfExists('inventory_adjustments');
        Schema::dropIfExists('vendor_inventories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
