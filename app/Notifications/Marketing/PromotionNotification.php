<?php

declare(strict_types=1);

namespace App\Notifications\Marketing;

use App\Notifications\PushNotification;
use NotificationChannels\Fcm\FcmMessage;

class PromotionNotification extends PushNotification
{
    /**
     * Create a new notification instance.
     *
     * @param  array<string, mixed>  $extraData
     */
    public function __construct(
        string $title,
        string $body,
        public ?string $deepLink = null,
        array $extraData = []
    ) {
        $data = array_merge([
            'type' => 'promotion',
            'route' => $deepLink ?? '/home',
        ], $extraData);

        parent::__construct(
            title: $title,
            body: $body,
            data: $data
        );
    }

    /**
     * Create the FCM message representation.
     */
    public function toFcm(object $notifiable): FcmMessage
    {
        return parent::toFcm($notifiable);
    }
}
