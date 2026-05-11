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
        Schema::table('product_categories', static function (Blueprint $table) {
            // Rename name to name_en
            if (Schema::hasColumn('product_categories', 'name')) {
                $table->renameColumn('name', 'name_en');
            }
        });

        Schema::table('product_categories', static function (Blueprint $table) {
            // Add other fields
            $table->string('name_km')->nullable()->after('name_en');
            $table->text('description_en')->nullable()->after('name_km');
            $table->text('description_km')->nullable()->after('description_en');
            $table->string('image_url')->nullable()->after('description_km');
            $table->boolean('is_active')->default(true)->after('image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', static function (Blueprint $table) {
            $table->dropColumn([
                'name_km',
                'description_en',
                'description_km',
                'image_url',
                'is_active',
            ]);
            $table->renameColumn('name_en', 'name');
        });
    }
};
