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
        // Notifications
        Schema::create('notifications', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // AI Chat
        Schema::create('ai_chat_sessions', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('ai_chat_messages', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('ai_chat_sessions')->cascadeOnDelete();
            $table->string('role'); // user, assistant, system
            $table->text('content');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['session_id', 'created_at']);
        });

        // Support Tickets
        Schema::create('support_tickets', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->string('status')->default('open');
            $table->string('priority')->default('normal');
            $table->timestamps();
        });

        Schema::create('support_messages', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users');
            $table->text('message');
            $table->string('file_path')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Finance & Payouts
        Schema::create('exchange_rates', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_currency_id');
            $table->unsignedBigInteger('to_currency_id');
            $table->decimal('rate', 16, 8);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exchange_rate_histories', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exchange_rate_id');
            $table->unsignedBigInteger('from_currency_id');
            $table->unsignedBigInteger('to_currency_id');
            $table->decimal('rate', 16, 8);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payouts', static function (Blueprint $table) {
            $table->id();
            $table->string('payout_number')->unique();
            $table->foreignId('vendor_id')->constrained('users');
            $table->foreignId('currency_id')->constrained();
            $table->decimal('amount', 16, 2);
            $table->foreignId('status_id')->constrained('payout_statuses');
            $table->foreignId('payout_method_id')->constrained('payout_methods');
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // System Settings
        Schema::create('settings', static function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        Schema::create('panel_preferences', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('panel_id');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'panel_id']);
        });

        Schema::create('user_devices', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_id')->unique();
            $table->string('fcm_token')->nullable();
            $table->string('platform')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('panel_preferences');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('exchange_rate_histories');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
        Schema::dropIfExists('notifications');
    }
};
