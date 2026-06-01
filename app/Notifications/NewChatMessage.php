<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewChatMessage extends PushNotification
{
    public function __construct(\App\Models\Message $message)
    {
        $senderName = $message->sender->name ?? 'User';
        
        $body = $message->content;
        if (! $body && $message->file_path) {
            $body = 'Sent an attachment';
        }

        parent::__construct(
            title: "New message from {$senderName}",
            body: $body,
            data: [
                'type' => 'chat_message',
                'route' => '/support_chat',
                'arguments' => json_encode(['conversation_id' => $message->conversation_id]),
            ]
        );
    }
}
