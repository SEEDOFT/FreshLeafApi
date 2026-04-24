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
        Schema::table('vendor_profiles', static function (Blueprint $table) {
            $table->string('id_card_front')->nullable();
            $table->string('id_card_back')->nullable();
            $table->string('store_front_image')->nullable();
            $table->string('organic_certificate_url')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_profiles', static function (Blueprint $table) {
            $table->dropColumn([
                'id_card_front',
                'id_card_back',
                'store_front_image',
                'organic_certificate_url',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
            ]);
        });
    }
};
