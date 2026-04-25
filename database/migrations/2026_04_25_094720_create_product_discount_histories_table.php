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
        Schema::create('product_discount_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_discount_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('old_percentage');
            $table->unsignedTinyInteger('new_percentage');
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_discount_histories');
    }
};
