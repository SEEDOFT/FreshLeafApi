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
        Schema::create('wallet_transaction_histories', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_transaction_id');
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('wallet_transaction_type_id');
            $table->unsignedBigInteger('wallet_transaction_status_id');
            $table->decimal('amount', 16, 2);
            $table->unsignedBigInteger('payment_method_id');
            $table->datetime('transaction_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transaction_histories');
    }
};
