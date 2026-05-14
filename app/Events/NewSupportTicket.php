<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\SupportTicket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Override;

class NewSupportTicket implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public SupportTicket $ticket
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    #[Override]
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support.admin'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'NewSupportTicket';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->ticket->id,
            'user_id' => $this->ticket->user_id,
            'user_name' => $this->ticket->user->fullName,
            'status' => $this->ticket->status,
            'created_at' => $this->ticket->created_at?->toIso8601String(),
        ];
    }
}
