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
            $table->id();
            $table->string('name_en');
            $table->string('name_km')->nullable();
            $table->string('slug')->unique();
            $table->text('description_en')->nullable();
            $table->text('description_km')->nullable();
            $table->string('image_url')->nullable();
            $table->foreignId('product_category_status_id')->constrained();
            $table->timestamps();
        });

        Schema::create('products', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained('product_categories');
            $table->foreignId('product_type_id')->constrained();
            $table->foreignId('default_unit_id')->constrained('units');
            $table->foreignId('product_status_id')->constrained();
            $table->string('name_en');
            $table->string('name_km')->nullable();
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
            $table->foreignId('vendor_id')->constrained('users');
            $table->foreignId('product_id')->constrained();
            $table->foreignId('unit_id')->constrained();
            $table->foreignId('inventory_status_id')->constrained('vendor_inventory_statuses');
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
            $table->foreignId('vendor_inventory_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->decimal('quantity_change', 16, 2);
            $table->string('type'); // restock, damage, sale, adjustment
            $table->string('reason');
            $table->string('proof_image_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('product_discounts', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->decimal('discount_value', 16, 2);
            $table->string('discount_type'); // percentage, fixed
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
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
