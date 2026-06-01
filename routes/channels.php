<?php

declare(strict_types=1);

use App\Broadcasting\AiChatChannel;
use App\Broadcasting\UserChannel;
use App\Models\ConversationParticipant;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    channel: 'ai-chat.{userId}.{sessionId}',
    callback: AiChatChannel::class,
    options: ['guards' => ['web', 'api', 'sanctum']]
);

Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    return ConversationParticipant::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->exists();
}, ['guards' => ['web', 'api', 'sanctum']]);

Broadcast::channel(
    channel: 'App.Models.User.{id}',
    callback: UserChannel::class,
    options: ['guards' => ['web', 'api', 'sanctum']]
);

Broadcast::channel('vendor.orders.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['web', 'api', 'sanctum']]);
