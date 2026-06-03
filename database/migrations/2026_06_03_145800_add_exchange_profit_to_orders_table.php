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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_currency_id')->nullable()->after('currency_id');
            $table->decimal('exchange_rate_applied', 16, 8)->nullable()->after('payment_currency_id');
            $table->decimal('exchange_profit_amount', 16, 2)->default(0)->after('exchange_rate_applied');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_currency_id', 'exchange_rate_applied', 'exchange_profit_amount']);
        });
    }
};
