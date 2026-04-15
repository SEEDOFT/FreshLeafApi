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
        Schema::create('payment_method_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('payment_method_statuses')->insert([
            ['id' => 1, 'code' => 'active', 'name' => 'Active'],
            ['id' => 2, 'code' => 'inactive', 'name' => 'Inactive'],
            ['id' => 3, 'code' => 'deleted', 'name' => 'Deleted'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_statuses');
    }
};
