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
        Schema::create('vendor_types', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        DB::table('vendor_types')->insert([
            ['id' => 1, 'name' => 'Standart'],
            ['id' => 2, 'name' => 'Premium'],
            ['id' => 3, 'name' => 'Enterprise'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_types');
    }
};
