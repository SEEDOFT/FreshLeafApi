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
        Schema::table('products', function (Blueprint $table) {
            // In SQLite, we need to be careful with dropping columns that have foreign keys.
            // Laravel's SQLite driver handles most of this, but we'll try to drop the FK first if possible.
            $table->dropForeign(['organic_category_id']);
            $table->dropColumn('organic_category_id');
        });

        Schema::dropIfExists('categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_km');
            $table->text('description_en')->nullable();
            $table->text('description_km')->nullable();
            $table->string('slug')->unique();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('organic_category_id')->nullable()->constrained('categories')->nullOnDelete();
        });
    }
};
