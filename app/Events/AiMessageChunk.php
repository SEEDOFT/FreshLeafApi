<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class AiMessageChunk implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $userId,
        public string $sessionId,
        public string $messageId,
        public string $role,
        public string $textChunk,
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
        return 'AiMessageChunk';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'message_id' => $this->messageId,
            'role' => $this->role,
            'text_chunk' => $this->textChunk,
            'timestamp' => $this->timestamp,
            'sequence' => $this->sequence,
        ];
    }
}
