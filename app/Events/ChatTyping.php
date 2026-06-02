<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTyping implements ShouldBroadcastNow
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

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $sender = User::find($this->senderId);

        return [
            'conversation_id' => $this->conversationId,
            'conversationId' => $this->conversationId,
            'sender_id' => $this->senderId,
            'senderId' => $this->senderId,
            'sender_name' => $sender->fullName ?? 'User',
            'senderName' => $sender->fullName ?? 'User',
        ];
    }
}
