<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('operation_profiles', 'vendor_profiles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('vendor_profiles', 'operation_profiles');
    }
};
