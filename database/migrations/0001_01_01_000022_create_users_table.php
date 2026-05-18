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
        Schema::create('users', static function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->datetime('email_verified_at')->nullable();
            $table->unsignedBigInteger('user_type_id');
            $table->unsignedBigInteger('user_status_id');
            $table->string('phone_number');
            $table->datetime('phone_number_verified_at')->nullable();
            $table->string('image')->default('user.png');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['email', 'user_type_id']);
            $table->unique(['phone_number', 'user_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
