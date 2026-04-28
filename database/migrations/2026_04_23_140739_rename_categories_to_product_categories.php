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

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite doesn't rename indexes with the table.
            // We need to drop the old ones and create new ones with the new table name.
            Schema::table('product_categories', static function (Blueprint $table) {
                // We use raw SQL to drop if they exist to avoid Laravel's name calculation issues in this specific state
                $db = Schema::getConnection()->getPdo();
                $db->exec('DROP INDEX IF EXISTS categories_slug_unique');
                $db->exec('DROP INDEX IF EXISTS categories_slug_index');

                $table->unique('slug', 'product_categories_slug_unique');
                $table->index('slug', 'product_categories_slug_index');
            });
        } else {
            Schema::table('product_categories', static function (Blueprint $table) {
                $table->renameIndex('categories_slug_unique', 'product_categories_slug_unique');
                $table->renameIndex('categories_slug_index', 'product_categories_slug_index');
            });
        }

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

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('categories', static function (Blueprint $table) {
                $db = Schema::getConnection()->getPdo();
                $db->exec('DROP INDEX IF EXISTS product_categories_slug_unique');
                $db->exec('DROP INDEX IF EXISTS product_categories_slug_index');

                $table->unique('slug', 'categories_slug_unique');
                $table->index('slug', 'categories_slug_index');
            });
        }
    }
};
