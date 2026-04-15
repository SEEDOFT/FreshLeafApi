<?php

declare(strict_types=1);

use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('ai-chat.{userId}.{sessionId}', static function (User $user, int $userId, string $sessionId): bool {
    if ($user->id !== $userId) {
        return false;
    }

    return AiChatSession::query()
        ->where('session_id', $sessionId)
        ->where('user_id', $userId)
        ->exists();
});
