<?php

declare(strict_types=1);

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
        Schema::create('currencies', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name', 100);
            $table->string('code', 3)->unique();
            $table->string('symbol', 10);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('currencies')->insert([
            [
                'id' => 1,
                'code' => 'KHR',
                'name' => 'Cambodian Riel',
                'symbol' => '៛',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
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
        Schema::dropIfExists('currencies');
    }
};
