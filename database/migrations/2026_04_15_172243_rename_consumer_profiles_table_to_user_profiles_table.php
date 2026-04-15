<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('consumer_profiles') && ! Schema::hasTable('user_profiles')) {
            Schema::rename('consumer_profiles', 'user_profiles');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_profiles') && ! Schema::hasTable('consumer_profiles')) {
            Schema::rename('user_profiles', 'consumer_profiles');
        }
    }
};
