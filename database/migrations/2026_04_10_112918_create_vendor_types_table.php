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
        Schema::create('vendor_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        $vendorTypes = [
            ['id' => 1, 'name' => 'Standart'],
            ['id' => 2, 'name' => 'Premium'],
            ['id' => 3, 'name' => 'Enterprise'],
        ];

        foreach ($vendorTypes as $type) {
            DB::table('vendor_types')->updateOrInsert(
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
        Schema::dropIfExists('vendor_types');
    }
};
