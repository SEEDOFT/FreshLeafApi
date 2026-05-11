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
        // User Profiles
        Schema::create('user_profiles', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('pin')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('locale')->default('en');
            $table->string('prefer_theme')->default('light');
            $table->timestamps();
        });

        // Admin Profiles
        Schema::create('admin_profiles', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('department')->nullable();
            $table->string('job_title')->nullable();
            $table->string('office_phone')->nullable();
            $table->boolean('super_admin')->default(false);
            $table->text('permissions')->nullable();
            $table->string('locale')->default('en');
            $table->string('prefer_theme')->default('light');
            $table->timestamps();
        });

        // Vendor Profiles
        Schema::create('vendor_profiles', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->string('shop_description')->nullable();
            $table->string('contact_phone');
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('address')->nullable();
            $table->string('id_card_front')->nullable();
            $table->string('id_card_back')->nullable();
            $table->string('store_front_image')->nullable();
            $table->string('organic_certificate_url')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_qr_code')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->boolean('is_open')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('approved_by_admin_id')->nullable()->constrained('users');
            $table->foreignId('rejected_by_admin_id')->nullable()->constrained('users');
            $table->text('approve_reason')->nullable();
            $table->text('reject_reason')->nullable();
            $table->text('meta')->nullable();
            $table->string('locale')->default('en');
            $table->string('prefer_theme')->default('light');
            $table->timestamps();
        });

        // Addresses
        Schema::create('addresses', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->default('Home');
            $table->string('recipient_name');
            $table->string('phone');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('province');
            $table->string('postal_code')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('long', 11, 8)->nullable();
            $table->string('address_map')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Wallets and Histories
        Schema::create('wallets', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained();
            $table->decimal('balance', 16, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('wallet_histories', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('currency_id')->constrained();
            $table->decimal('balance', 16, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_histories');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('vendor_profiles');
        Schema::dropIfExists('admin_profiles');
        Schema::dropIfExists('user_profiles');
    }
};
