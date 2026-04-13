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
        Schema::create('vendor_statuses', static function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        DB::table('vendor_statuses')->insert([
            ['id' => 1, 'name' => 'Active'],
            ['id' => 2, 'name' => 'Inactive'],
            ['id' => 3, 'name' => 'Pending'],
            ['id' => 4, 'name' => 'Suspended'],
            ['id' => 5, 'name' => 'Rejected'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_statuses');
    }
};
