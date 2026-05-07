<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SupportTicket;
use NotificationChannels\Fcm\FcmMessage;

class NewSupportTicketNotification extends PushNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public SupportTicket $ticket
    ) {
        parent::__construct(
            title: 'New Support Ticket',
            body: "User {$this->ticket->user->fullName} has started a new support chat.",
            data: [
                'type' => 'support_chat',
                'ticket_id' => (string) $this->ticket->id,
                'route' => '/support_chat',
            ]
        );
    }

    /**
     * Create the FCM message representation.
     */
    public function toFcm(): FcmMessage
    {
        return parent::toFcm();
    }
}
