<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\SupportMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Override;

class SupportMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public SupportMessage $message
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    #[Override]
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('support.ticket.'.$this->message->support_ticket_id),
        ];

        // Only broadcast to the admin channel if it's from a user
        // (Admins don't need to notify other admins about their own replies via the admin channel)
        if ($this->message->sender_type === 'user') {
            $channels[] = new PrivateChannel('support.admin');
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'SupportMessageSent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'support_ticket_id' => $this->message->support_ticket_id,
            'sender_type' => $this->message->sender_type,
            'sender_id' => $this->message->sender_id,
            'message' => $this->message->message,
            'file_path' => $this->message->file_path,
            'created_at' => $this->message->created_at?->toIso8601String(),
            'updated_at' => $this->message->updated_at?->toIso8601String(),
        ];
    }
}
