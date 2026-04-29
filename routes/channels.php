<?php

declare(strict_types=1);

use App\Models\AiChatSession;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('ai-chat.{userId}.{sessionId}', static function (User $user, string $userId, string $sessionId): bool {
    if (! hash_equals((string) $user->id, $userId)) {
        return false;
    }

    return AiChatSession::where('session_id', $sessionId)
        ->where('user_id', (int) $user->id)
        ->exists();
});

Broadcast::channel('support.ticket.{ticketId}', static function (User $user, string $ticketId): bool {
    $ticket = SupportTicket::find((int) $ticketId);

    if (! $ticket) {
        return false;
    }

    return (int) $user->id === (int) $ticket->user_id || $user->isType(UserType::ADMIN);
});

Broadcast::channel('support.admin', static function (User $user): bool {
    return $user->isType(UserType::ADMIN);
});
