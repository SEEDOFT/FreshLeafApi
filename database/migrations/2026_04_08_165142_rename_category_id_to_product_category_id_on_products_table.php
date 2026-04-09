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
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->renameColumn('category_id', 'product_category_id');
            $table->foreign('product_category_id')
                ->references('id')
                ->on('product_categories')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_category_id']);
            $table->renameColumn('product_category_id', 'category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('product_categories')
                ->restrictOnDelete();
        });
    }
};
