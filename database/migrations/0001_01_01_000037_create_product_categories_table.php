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
            $table->unsignedBigInteger('id')->primary();
            $table->string('name_en');
            $table->string('name_km');
            $table->string('slug')->unique();
            $table->text('description_en')->nullable();
            $table->text('description_km')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedBigInteger('product_category_status_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
