<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('user_types')) {
            return;
        }

        DB::table('user_types')->updateOrInsert(['id' => 1], ['name' => 'User']);
        DB::table('user_types')->updateOrInsert(['id' => 2], ['name' => 'Vendor']);
        DB::table('user_types')->updateOrInsert(['id' => 3], ['name' => 'Admin']);

        DB::table('user_types')
            ->whereNotIn('id', [1, 2, 3])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('user_types')) {
            return;
        }

        DB::table('user_types')->updateOrInsert(['id' => 1], ['name' => 'Consumer']);
        DB::table('user_types')->updateOrInsert(['id' => 2], ['name' => 'Premium Consumer']);
        DB::table('user_types')->where('id', 3)->delete();
    }
};
