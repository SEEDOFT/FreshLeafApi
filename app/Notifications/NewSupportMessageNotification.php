<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SupportMessage;
use Illuminate\Support\Str;
use NotificationChannels\Fcm\FcmMessage;

class NewSupportMessageNotification extends PushNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public SupportMessage $supportMessage
    ) {
        parent::__construct(
            title: __('api.notifications.new_support_message_title'),
            body: Str::limit($this->supportMessage->message, 50),
            data: [
                'type' => 'support_chat',
                'ticket_id' => (string) $this->supportMessage->support_ticket_id,
                'route' => '/support_chat',
                'message_id' => (string) $this->supportMessage->id,
                'sender_type' => $this->supportMessage->sender_type,
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
