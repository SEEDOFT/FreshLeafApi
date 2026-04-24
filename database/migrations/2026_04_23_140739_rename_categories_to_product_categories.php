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
        Schema::rename('categories', 'product_categories');

        Schema::table('products', static function (Blueprint $table) {
            $table->renameColumn('category_id', 'product_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', static function (Blueprint $table) {
            $table->renameColumn('product_category_id', 'category_id');
        });

        Schema::rename('product_categories', 'categories');
    }
};
