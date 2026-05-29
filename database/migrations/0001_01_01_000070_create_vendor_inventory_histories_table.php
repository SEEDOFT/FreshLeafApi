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
        Schema::create('vendor_inventory_histories', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_inventory_id');
            $table->decimal('quantity_change', 16, 4);
            $table->decimal('new_quantity', 16, 4);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('vendor_inventory_id');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_inventory_histories');
    }
};
