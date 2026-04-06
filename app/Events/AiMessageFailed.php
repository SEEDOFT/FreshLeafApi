<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiMessageFailed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $sessionId,
        public string $messageId,
        public string $role,
        public string $error,
        public int $sequence,
        public string $timestamp,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ai-chat.'.$this->userId.'.'.$this->sessionId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AiMessageFailed';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'message_id' => $this->messageId,
            'role' => $this->role,
            'error' => $this->error,
            'timestamp' => $this->timestamp,
            'sequence' => $this->sequence,
        ];
    }
}
