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
        Schema::table('ai_chat_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'session_id'], 'ai_chat_sessions_user_session_idx');
        });

        Schema::table('ai_chat_messages', function (Blueprint $table) {
            $table->index(['ai_chat_session_id', 'message_id'], 'ai_chat_messages_session_message_idx');
            $table->index(['ai_chat_session_id', 'role', 'id'], 'ai_chat_messages_session_role_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_chat_sessions', function (Blueprint $table) {
            $table->dropIndex('ai_chat_sessions_user_session_idx');
        });

        Schema::table('ai_chat_messages', function (Blueprint $table) {
            $table->dropIndex('ai_chat_messages_session_message_idx');
            $table->dropIndex('ai_chat_messages_session_role_id_idx');
        });
    }
};
