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
        Schema::create('user_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('user_statuses')->insert([
            [
                'id' => 1,
                'code' => 'PENDING',
                'name' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'code' => 'ACTIVE',
                'name' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'code' => 'INACTIVE',
                'name' => 'INACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'code' => 'DELETED',
                'name' => 'DELETED',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_statuses');
    }
};
