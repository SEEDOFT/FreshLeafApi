<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', static function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable();
        });

        Schema::table('wallet_transaction_histories', static function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable();
        });

        // Backfill existing rows from their wallet's currency_id
        DB::statement(<<<'SQL'
            UPDATE wallet_transactions
            SET currency_id = (
                SELECT currency_id FROM wallets WHERE wallets.id = wallet_transactions.wallet_id
            )
            WHERE currency_id IS NULL
        SQL);

        DB::statement(<<<'SQL'
            UPDATE wallet_transaction_histories
            SET currency_id = (
                SELECT currency_id FROM wallets WHERE wallets.id = wallet_transaction_histories.wallet_id
            )
            WHERE currency_id IS NULL
        SQL);
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', static function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });

        Schema::table('wallet_transaction_histories', static function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });
    }
};
