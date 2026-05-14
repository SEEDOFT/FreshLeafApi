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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
