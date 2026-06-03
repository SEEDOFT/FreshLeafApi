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
        Schema::table('payouts', static function (Blueprint $table) {
            $table->foreignId('processed_by_admin_id')
                ->nullable()
                ->after('processed_at')
                ->constrained('users');

            $table->string('transaction_reference')
                ->nullable()
                ->after('processed_by_admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payouts', static function (Blueprint $table) {
            $table->dropForeign(['processed_by_admin_id']);
            $table->dropColumn(['processed_by_admin_id', 'transaction_reference']);
        });
    }
};
