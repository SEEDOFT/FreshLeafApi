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
            $table->unsignedBigInteger('payment_method_type_id')->after('id');
            $table->unsignedBigInteger('payment_method_status_id')->after('payment_method_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['payment_method_type_id', 'payment_method_status_id']);
        });
    }
};
