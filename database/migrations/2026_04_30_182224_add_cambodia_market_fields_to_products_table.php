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
            $table->string('province_of_origin')->nullable();
            $table->string('certification_type')->nullable();
            $table->text('storage_instructions_en')->nullable();
            $table->text('storage_instructions_km')->nullable();
            $table->string('packaging_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'province_of_origin',
                'certification_type',
                'storage_instructions_en',
                'storage_instructions_km',
                'packaging_type',
            ]);
        });
    }
};
