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

        DB::table('payment_method_types')->insert([
            ['id' => 1, 'code' => 'visa', 'name' => 'Visa'],
            ['id' => 2, 'code' => 'master_card', 'name' => 'MasterCard'],
            ['id' => 3, 'code' => 'union_pay', 'name' => 'UnionPay'],
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
