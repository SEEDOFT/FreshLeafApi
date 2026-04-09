<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class AiMessageCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $userId,
        public string $sessionId,
        public string $messageId,
        public string $role,
        public string $fullText,
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
        return 'AiMessageCompleted';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'message_id' => $this->messageId,
            'role' => $this->role,
            'full_text' => $this->fullText,
            'timestamp' => $this->timestamp,
            'sequence' => $this->sequence,
        ];
    }
}
