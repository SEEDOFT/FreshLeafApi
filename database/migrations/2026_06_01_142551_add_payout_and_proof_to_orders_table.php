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
            $table->boolean('is_vendor_paid')->default(false);
            $table->unsignedBigInteger('vendor_payout_transaction_id')->nullable();
            $table->string('delivery_proof_photo')->nullable();
            $table->datetime('consumer_confirmed_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'is_vendor_paid',
                'vendor_payout_transaction_id',
                'delivery_proof_photo',
                'consumer_confirmed_date',
            ]);
        });
    }
};
