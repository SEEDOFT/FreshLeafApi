<?php

declare(strict_types=1);

use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('ai-chat.{userId}.{sessionId}', static function (User $user, string $userId, string $sessionId): bool {
    if (! hash_equals((string) $user->id, $userId)) {
        return false;
    }

    return AiChatSession::where('session_id', $sessionId)
        ->where('user_id', (int) $user->id)
        ->exists();
});
