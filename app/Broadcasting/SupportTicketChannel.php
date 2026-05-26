<?php

declare(strict_types=1);

namespace App\Broadcasting;

use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserType;

class SupportTicketChannel
{
    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user, string $ticketId): bool
    {
        $ticket = SupportTicket::find((int) $ticketId);

        if (! $ticket) {
            return false;
        }

        return $user->id === (int) $ticket->user_id ||
            $user->isType(UserType::ADMIN_ID);
    }
}
