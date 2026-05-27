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
        Schema::table('orders', static function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('payment_status_id');
            $table->unsignedBigInteger('payment_id')->nullable()->after('currency_id');
            $table->datetime('place_order_date')->nullable()->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', static function (Blueprint $table) {
            $table->dropColumn(['currency_id', 'payment_id', 'place_order_date']);
        });
    }
};
