<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class PushNotification extends Notification
{
    /**
     * Create a new notification instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $title,
        public string $body,
        public array $data = [],
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return [
            DatabaseChannel::class,
            FcmChannel::class,
        ];
    }

    /**
     * Create the FCM message representation.
     */
    public function toFcm(): FcmMessage
    {
        return new FcmMessage(notification: new FcmNotification(
            title: $this->title,
            body: $this->body,
        ))
            ->data($this->data)
            ->custom([
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'high_importance_channel',
                        'color' => '#4ade80',
                        'sound' => 'default',
                    ],
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'freshleaf_push',
                    ],
                ],
            ]);
    }
}
