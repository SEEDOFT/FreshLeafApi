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
        Schema::create('payouts', static function (Blueprint $table) {
            $table->id();
            $table->string('payout_number')->unique();
            $table->foreignId('vendor_id')->constrained('users');
            $table->foreignId('currency_id')->constrained();
            $table->decimal('amount', 16, 2);
            $table->foreignId('status_id')->constrained('payout_statuses');
            $table->foreignId('payout_method_id')->constrained('payout_methods');
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
