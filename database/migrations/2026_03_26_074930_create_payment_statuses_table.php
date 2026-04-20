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
        Schema::create('payment_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('payment_statuses')->insert([
            [
                'id' => 1,
                'code' => 'ACTIVE',
                'name' => 'ACTIVE',
            ],
            [
                'id' => 2,
                'code' => 'INACTIVE',
                'name' => 'INACTIVE',
            ],
            [
                'id' => 3,
                'code' => 'DELETE',
                'name' => 'DELETE',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_statuses');
    }
};
