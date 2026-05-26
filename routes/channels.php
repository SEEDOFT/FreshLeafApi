<?php

declare(strict_types=1);

use App\Broadcasting\AiChatChannel;
use App\Broadcasting\SupportAdminChannel;
use App\Broadcasting\SupportTicketChannel;
use App\Broadcasting\UserChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    channel: 'ai-chat.{userId}.{sessionId}',
    callback: AiChatChannel::class,
    options: ['guards' => ['web', 'api', 'sanctum']]
);

Broadcast::channel(
    channel: 'support.ticket.{ticketId}',
    callback: SupportTicketChannel::class,
    options: ['guards' => ['web', 'api', 'sanctum']]
);

Broadcast::channel(
    channel: 'support.admin',
    callback: SupportAdminChannel::class,
    options: ['guards' => ['web', 'api', 'sanctum']]
);

Broadcast::channel(
    channel: 'App.Models.User.{id}',
    callback: UserChannel::class,
    options: ['guards' => ['web', 'api', 'sanctum']]
);
