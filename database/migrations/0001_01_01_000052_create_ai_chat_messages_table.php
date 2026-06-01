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
        Schema::create('ai_chat_messages', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_chat_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('message_id')->unique();
            $table->string('role');
            $table->longText('content')->default('');
            $table->string('status')->default('pending');
            $table->unsignedInteger('sequence')->default(0);
            $table->text('error')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['ai_chat_session_id', 'created_at']);
            $table->index(['ai_chat_session_id', 'sequence']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
    }
};
