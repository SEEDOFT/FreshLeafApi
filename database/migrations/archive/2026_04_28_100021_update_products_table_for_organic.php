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
        Schema::table('products', static function (Blueprint $table) {
            // Rename existing fields for localization consistency if they exist
            if (Schema::hasColumn('products', 'name')) {
                $table->renameColumn('name', 'name_en');
            }
            if (Schema::hasColumn('products', 'description')) {
                $table->renameColumn('description', 'description_en');
            }
        });

        Schema::table('products', static function (Blueprint $table) {
            // Add new localized and organic fields as nullable for SQLite compatibility
            $table->string('name_km')->nullable()->after('name_en');
            $table->text('description_km')->nullable()->after('description_en');

            // Link to the NEW categories table
            $table->foreignId('organic_category_id')->nullable()->after('product_category_id')->constrained('categories')->nullOnDelete();

            // Organic traceability and pricing
            $table->string('selling_unit')->nullable()->after('default_unit_id'); // e.g., kg, bunch
            $table->decimal('price_per_unit', 15, 4)->nullable()->after('selling_unit');
            $table->decimal('available_stock', 15, 4)->default(0)->after('price_per_unit');
            $table->string('farm_name_location')->nullable()->after('available_stock');
            $table->enum('farming_method', ['certified_organic', 'pesticide_free', 'naturally_grown'])->nullable()->after('farm_name_location');
            $table->date('harvest_date')->nullable()->after('farming_method');
            $table->boolean('is_active')->default(true)->after('harvest_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('organic_category_id');
            $table->dropColumn([
                'name_km',
                'description_km',
                'selling_unit',
                'price_per_unit',
                'available_stock',
                'farm_name_location',
                'farming_method',
                'harvest_date',
                'is_active',
            ]);
            $table->renameColumn('name_en', 'name');
            $table->renameColumn('description_en', 'description');
        });
    }
};
