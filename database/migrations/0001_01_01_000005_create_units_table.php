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
        Schema::create('units', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name_en');
            $table->string('name_km');
            $table->string('symbol');
            $table->decimal('conversion_to_base', 16, 8)->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
