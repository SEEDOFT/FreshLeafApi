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
        Schema::table('vendor_inventories', static function (Blueprint $table) {
            $table->timestamp('expiring_alert_sent_at')->nullable()->after('batch_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_inventories', static function (Blueprint $table) {
            $table->dropColumn('expiring_alert_sent_at');
        });
    }
};
