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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropForeign(['payment_type_id']);
            $table->dropForeign(['payment_status_id']);
            $table->dropColumn(['payment_type_id', 'payment_status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->integer('payment_type_id')->nullable();
            $table->integer('payment_status_id')->nullable();

            $table->foreign('payment_type_id')->references('id')->on('payment_types')->restrictOnDelete();
            $table->foreign('payment_status_id')->references('id')->on('payment_statuses')->restrictOnDelete();
        });
    }
};
