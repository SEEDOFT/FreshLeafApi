<?php

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
        Schema::create('vendors', static function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('password', 255);
            $table->string('remember_token', 191)->nullable();
            $table->unsignedBigInteger('vendor_type_id');
            $table->unsignedBigInteger('vendor_status_id');
            $table->string('business_name');
            $table->string('phone_number', 40)->unique();
            $table->string('city', 120)->nullable();
            $table->string('province', 120)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->json('meta')->nullable();
            $table->index('vendor_status_id');
            $table->index('vendor_type_id');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('admins', static function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('password', 255);
            $table->string('remember_token', 191)->nullable();
            $table->unsignedBigInteger('admin_type_id');
            $table->unsignedBigInteger('admin_status_id');
            $table->string('phone_number', 40)->unique();
            $table->string('department', 120)->nullable();
            $table->string('job_title', 120)->nullable();
            $table->string('office_phone', 40)->nullable();
            $table->json('permissions')->nullable();
            $table->index('admin_status_id');
            $table->index('admin_type_id');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('vendors');
    }
};
