<?php

declare(strict_types=1);

namespace App\Broadcasting;

use App\Models\AiChatSession;
use App\Models\User;

class AiChatChannel
{
    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user, string $userId, string $sessionId): bool
    {
        if ($user->id !== (int) $userId) {
            return false;
        }

        return AiChatSession::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->exists();
    }
}
