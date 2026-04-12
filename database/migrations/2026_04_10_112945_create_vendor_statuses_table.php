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
        Schema::create('vendor_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        $vendorStatuses = [
            ['id' => 1, 'name' => 'Active'],
            ['id' => 2, 'name' => 'Inactive'],
            ['id' => 3, 'name' => 'Pending'],
            ['id' => 4, 'name' => 'Suspended'],
            ['id' => 5, 'name' => 'Rejected'],
        ];

        foreach ($vendorStatuses as $status) {
            DB::table('vendor_statuses')->updateOrInsert(
                ['id' => $status['id']],
                $status + ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_statuses');
    }
};
