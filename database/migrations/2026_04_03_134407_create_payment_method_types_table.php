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
        Schema::create('payment_method_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();

        DB::table('payment_method_types')->insert([
            ['id' => 1, 'code' => 'wallet', 'name' => 'Wallet', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'credit_debit', 'name' => 'Credit / Debit Card', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'aba', 'name' => 'ABA', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'acleda', 'name' => 'ACLEDA', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_types');
    }
};
