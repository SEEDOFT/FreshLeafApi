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
        // 1. Add fields to vendor_inventories
        Schema::table('vendor_inventories', static function (Blueprint $table) {
            $table->string('province_of_origin')->nullable();
            $table->string('certification_type')->nullable();
            $table->string('packaging_type')->nullable();
            $table->integer('shelf_life_days')->nullable();
        });

        // 2. Drop fields from products
        Schema::table('products', static function (Blueprint $table) {
            $colsToDrop = [
                'province_of_origin',
                'certification_type',
                'storage_instructions_en',
                'storage_instructions_km',
                'packaging_type',
                'shelf_life_days',
            ];

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
            $table->string('province_of_origin')->nullable();
            $table->string('certification_type')->nullable();
            $table->text('storage_instructions_en')->nullable();
            $table->text('storage_instructions_km')->nullable();
            $table->string('packaging_type')->nullable();
            $table->integer('shelf_life_days')->nullable();
        });

        Schema::table('vendor_inventories', static function (Blueprint $table) {
            $table->dropColumn([
                'province_of_origin',
                'certification_type',
                'packaging_type',
                'shelf_life_days',
            ]);
        });
    }
};
