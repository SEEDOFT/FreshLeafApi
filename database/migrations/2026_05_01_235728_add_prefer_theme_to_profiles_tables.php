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
        Schema::table('admin_profiles', static function (Blueprint $table): void {
            $table->string('prefer_theme')->nullable()->default('system')->after('locale');
        });

        Schema::table('user_profiles', static function (Blueprint $table): void {
            $table->string('prefer_theme')->nullable()->default('system')->after('locale');
        });

        Schema::table('vendor_profiles', static function (Blueprint $table): void {
            $table->string('prefer_theme')->nullable()->default('system')->after('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_profiles', static function (Blueprint $table): void {
            $table->dropColumn('prefer_theme');
        });

        Schema::table('user_profiles', static function (Blueprint $table): void {
            $table->dropColumn('prefer_theme');
        });

        Schema::table('vendor_profiles', static function (Blueprint $table): void {
            $table->dropColumn('prefer_theme');
        });
    }
};
