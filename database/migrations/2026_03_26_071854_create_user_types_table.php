<?php

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
        Schema::create('user_types', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('user_types')->insert([
            [
                'id' => 1,
                'code' => 'CONSUMER',
                'name' => 'CONSUMER',
            ],
            [
                'id' => 2,
                'code' => 'OPERATION',
                'name' => 'OPERATION',
            ],
            [
                'id' => 3,
                'code' => 'ADMIN',
                'name' => 'ADMIN',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_types');
    }
};
