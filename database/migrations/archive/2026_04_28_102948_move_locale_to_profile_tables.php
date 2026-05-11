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
        // 1. Add locale to admin_profiles and vendor_profiles
        Schema::table('admin_profiles', static function (Blueprint $table) {
            $table->string('locale', 10)->default('en')->after('user_id');
        });

        Schema::table('vendor_profiles', static function (Blueprint $table) {
            $table->string('locale', 10)->default('en')->after('user_id');
        });

        // 2. Standardize user_profiles field name
        Schema::table('user_profiles', static function (Blueprint $table) {
            if (Schema::hasColumn('user_profiles', 'preferred_language')) {
                $table->renameColumn('preferred_language', 'locale');
            } else {
                $table->string('locale', 10)->default('en')->after('user_id');
            }
        });

        // 3. Data Migration: Copy locale from users to profile tables
        $users = DB::table('users')->select('id', 'locale', 'user_type_id')->get();

        foreach ($users as $user) {
            if (empty($user->locale)) {
                continue;
            }

            // UserType IDs: 1 = admin, 2 = vendor, 3 = consumer (adjust based on project constants if known)
            // Checking by table existence to be safe
            DB::table('admin_profiles')->where('user_id', $user->id)->update(['locale' => $user->locale]);
            DB::table('vendor_profiles')->where('user_id', $user->id)->update(['locale' => $user->locale]);
            DB::table('user_profiles')->where('user_id', $user->id)->update(['locale' => $user->locale]);
        }

        // 4. Remove locale from users table
        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->string('locale', 10)->default('en')->after('phone_number');
        });

        Schema::table('admin_profiles', static function (Blueprint $table) {
            $table->dropColumn('locale');
        });

        Schema::table('vendor_profiles', static function (Blueprint $table) {
            $table->dropColumn('locale');
        });

        Schema::table('user_profiles', static function (Blueprint $table) {
            $table->renameColumn('locale', 'preferred_language');
        });
    }
};
