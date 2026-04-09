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
        Schema::table('consumer_profiles', function (Blueprint $table) {
            $table->string('pin')->nullable()->after('user_id');
        });

        $usersWithPin = DB::table('users')
            ->select(['id', 'pin'])
            ->whereNotNull('pin')
            ->get();

        foreach ($usersWithPin as $user) {
            DB::table('consumer_profiles')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'pin' => $user->pin,
                    'preferred_language' => 'en',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumer_profiles', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
};
