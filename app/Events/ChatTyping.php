<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;

    public int $senderId;

    public function __construct(int $conversationId, int $senderId)
    {
        $this->conversationId = $conversationId;
        $this->senderId = $senderId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.conversation.'.$this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ChatTyping';
    }
}
