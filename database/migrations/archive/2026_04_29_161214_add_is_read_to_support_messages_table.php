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
        Schema::table('support_messages', static function (Blueprint $table) {
            $table->boolean('is_read')->default(false);
            $table->index(['support_ticket_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_messages', static function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }
};
