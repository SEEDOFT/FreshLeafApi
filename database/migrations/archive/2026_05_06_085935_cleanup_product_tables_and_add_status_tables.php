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
        // 1. Create ProductCategoryStatus table
        if (! Schema::hasTable('product_category_statuses')) {
            Schema::create('product_category_statuses', static function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        // 2. Create VendorInventoryStatus table
        if (! Schema::hasTable('vendor_inventory_statuses')) {
            Schema::create('vendor_inventory_statuses', static function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        // 3. Update product_categories
        Schema::table('product_categories', static function (Blueprint $table) {
            if (! Schema::hasColumn('product_categories', 'product_category_status_id')) {
                $table->foreignId('product_category_status_id')->nullable()->constrained('product_category_statuses')->after('id');
            }
            if (Schema::hasColumn('product_categories', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        // 4. Update vendor_inventories
        Schema::table('vendor_inventories', static function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_inventories', 'inventory_status_id')) {
                $table->foreignId('inventory_status_id')->nullable()->constrained('vendor_inventory_statuses')->after('id');
            }
            if (Schema::hasColumn('vendor_inventories', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        // 5. Cleanup products table
        Schema::table('products', static function (Blueprint $table) {
            if (Schema::hasColumn('products', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            $colsToDrop = ['selling_unit', 'price_per_unit', 'available_stock', 'farm_name_location', 'farming_method', 'harvest_date', 'is_active', 'is_organic'];
            foreach ($colsToDrop as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', static function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('selling_unit')->nullable();
            $table->decimal('price_per_unit', 10, 2)->nullable();
            $table->decimal('available_stock', 10, 2)->default(0);
            $table->string('farm_name_location')->nullable();
            $table->string('farming_method')->nullable();
            $table->date('harvest_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_organic')->default(true);
        });

        Schema::table('vendor_inventories', static function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->dropConstrainedForeignId('inventory_status_id');
        });

        Schema::table('product_categories', static function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->dropConstrainedForeignId('product_category_status_id');
        });

        Schema::dropIfExists('vendor_inventory_statuses');
        Schema::dropIfExists('product_category_statuses');
    }
};
