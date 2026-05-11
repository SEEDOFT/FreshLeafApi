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
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->text('shop_description')->nullable()->after('business_name');
            $table->time('opening_time')->nullable()->after('address');
            $table->time('closing_time')->nullable()->after('opening_time');
            $table->boolean('is_open')->default(true)->after('closing_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropColumn(['shop_description', 'opening_time', 'closing_time', 'is_open']);
        });
    }
};
