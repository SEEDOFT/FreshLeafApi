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
        Schema::create('inventory_adjustments', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_inventory_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('quantity_change', 16, 2);
            $table->string('type');
            $table->string('reason');
            $table->string('proof_image_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
