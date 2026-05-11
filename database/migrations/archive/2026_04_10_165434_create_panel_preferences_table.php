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
        Schema::create('panel_preferences', static function (Blueprint $table) {
            $table->id();
            $table->morphs('account');
            $table->string('locale', 2)->default('km');
            $table->string('theme', 10)->default('light');
            $table->timestamps();

            $table->unique(['account_type', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panel_preferences');
    }
};
