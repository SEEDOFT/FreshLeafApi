<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Override;

class AiMessageStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $userId,
        public string $sessionId,
        public string $messageId,
        public string $role,
        public int $sequence,
        public string $timestamp,
    ) {}

    /**
     * {@inheritDoc}
     *
     * @return array<int, Channel>
     */
    #[Override]
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ai-chat.'.$this->userId.'.'.$this->sessionId),
        ];
    }

    /**
     * Broadcast the event with a custom name that the frontend can listen for,
     * instead of the default class name.
     */
    public function broadcastAs(): string
    {
        return 'AiMessageStarted';
    }

    /**
     * Broadcast the event with the necessary data for the frontend
     * to process and display the started AI message in real-time.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'message_id' => $this->messageId,
            'role' => $this->role,
            'timestamp' => $this->timestamp,
            'sequence' => $this->sequence,
        ];
    }
}
