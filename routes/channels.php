<?php

declare(strict_types=1);

use App\Models\AiChatSession;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    channel: 'ai-chat.{userId}.{sessionId}',
    callback: static function (
        User $user,
        string $userId,
        string $sessionId
    ): bool {
        if ($user->id !== (int) $userId) {
            return false;
        }

        return AiChatSession::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->exists();

    },
    options: ['guards' => ['web']]
);

Broadcast::channel(
    channel: 'support.ticket.{ticketId}',
    callback: static function (User $user, string $ticketId): bool {
        $ticket = SupportTicket::find((int) $ticketId);

        if (! $ticket) {
            return false;
        }

        return $user->id ===
            (int) $ticket->user_id ||
            $user->isType(UserType::ADMIN_ID);
    },

    options: ['guards' => ['web']]
);

Broadcast::channel(
    channel: 'support.admin',
    callback: static function (User $user): bool {
        return $user->isType(UserType::ADMIN_ID);
    },
    options: ['guards' => ['web']]
);
