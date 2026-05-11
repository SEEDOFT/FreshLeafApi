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
        Schema::create('vendor_profiles', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('business_name');
            $table->string('contact_phone')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('approved_by_admin_id')->nullable()->index();
            $table->unsignedBigInteger('rejected_by_admin_id')->nullable()->index();
            $table->text('approve_reason')->nullable();
            $table->text('reject_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_profiles');
    }
};
