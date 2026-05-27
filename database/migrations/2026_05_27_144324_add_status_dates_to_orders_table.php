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
            $table->datetime('order_pending_date')->nullable()->after('place_order_date');
            $table->datetime('order_confirmed_date')->nullable()->after('order_pending_date');
            $table->datetime('order_preparing_date')->nullable()->after('order_confirmed_date');
            $table->datetime('order_delivered_date')->nullable()->after('order_preparing_date');
            $table->datetime('order_cancelled_date')->nullable()->after('order_delivered_date');
            $table->datetime('order_awaiting_payment_date')->nullable()->after('order_cancelled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', static function (Blueprint $table) {
            $table->dropColumn([
                'order_pending_date',
                'order_confirmed_date',
                'order_preparing_date',
                'order_delivered_date',
                'order_cancelled_date',
                'order_awaiting_payment_date',
            ]);
        });
    }
};
