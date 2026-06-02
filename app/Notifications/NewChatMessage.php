<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Message;
use JsonException;

class NewChatMessage extends PushNotification
{
    /**
     * @throws JsonException
     */
    public function __construct(Message $message)
    {
        $message->loadMissing('sender');

        $senderName = $message->sender->fullName ?? 'User';

        $body = $message->content;
        if (! $body && $message->file_path) {
            $body = 'Sent an attachment';
        }

        parent::__construct(
            title: "New message from {$senderName}",
            body: $body,
            data: [
                'type' => 'chat_message',
                'conversation_id' => (string) $message->conversation_id,
                'message_id' => (string) $message->id,
                'sender_id' => (string) $message->sender_id,
                'sender_name' => $senderName,
                'message_preview' => $body,
                'route' => '/support_chat',
                'deep_link' => 'freshleaf://support-chat?conversation_id='.$message->conversation_id,
                'arguments' => json_encode([
                    'conversation_id' => $message->conversation_id,
                    'message_id' => $message->id,
                ], JSON_THROW_ON_ERROR),
            ]
        );
    }
}
