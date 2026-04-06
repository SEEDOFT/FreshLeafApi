<?php

use App\Models\AiChatSession;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('ai-chat.{userId}.{sessionId}', function (User $user, int $userId, string $sessionId): bool {
    if ($user->id !== $userId) {
        return false;
    }

    return AiChatSession::where('session_id', $sessionId)
        ->where('user_id', $userId)
        ->where('user_status_id', UserStatus::ACTIVE)
        ->where('user_type_id', UserType::CONSUMER)
        ->exists();
});
