<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_types', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('payment_types')->insert([
            [
                'id' => 1,
                'code' => 'VISA',
                'name' => 'VISA',
            ],
            [
                'id' => 2,
                'code' => 'MASTER_CARD',
                'name' => 'MASTER CARD',
            ],
            [
                'id' => 3,
                'code' => 'UNION_PAY',
                'name' => 'UNION PAY',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_types');
    }
};
