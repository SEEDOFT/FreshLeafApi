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
        Schema::create('wallet_histories', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('currency_id')->index();
            $table->string('action', 40);
            $table->decimal('amount_before', 16, 4);
            $table->decimal('amount_change', 16, 4);
            $table->decimal('amount_after', 16, 4);
            $table->unsignedBigInteger('performed_by_user_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'id']);
            $table->index('performed_by_user_id');

            $table->foreign('performed_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_histories', static function (Blueprint $table) {
            $table->dropForeign(['performed_by_user_id']);
        });
        Schema::dropIfExists('wallet_histories');
    }
};
