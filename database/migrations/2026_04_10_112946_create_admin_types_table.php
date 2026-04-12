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
        Schema::create('admin_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        $adminTypes = [
            ['id' => 1, 'name' => 'Super Admin'],
            ['id' => 2, 'name' => 'Operation'],
            ['id' => 3, 'name' => 'Support'],
        ];

        foreach ($adminTypes as $type) {
            DB::table('admin_types')->updateOrInsert(
                ['id' => $type['id']],
                $type + ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_types');
    }
};
