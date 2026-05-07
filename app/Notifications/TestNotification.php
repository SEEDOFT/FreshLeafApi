<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class TestNotification extends Notification
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
    public function via(object $notifiable): array
    {
        Log::info('TestNotification via() called', [
            'notifiable_id' => method_exists($notifiable, 'getKey') ? $notifiable->getKey() : 'unknown',
            'title' => $this->title,
        ]);

        return [FcmChannel::class];
    }

    /**
     * Create the FCM message representation.
     */
    public function toFcm(object $notifiable): FcmMessage
    {
        $notifiableId = method_exists($notifiable, 'getKey') ? $notifiable->getKey() : 'unknown';

        Log::info('TestNotification toFcm() called', [
            'notifiable_id' => $notifiableId,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'notifiable_class' => get_class($notifiable),
        ]);

        if (method_exists($notifiable, 'routeNotificationForFcm')) {
            $fcmTokens = $notifiable->routeNotificationForFcm();
            Log::info('TestNotification FCM tokens', [
                'notifiable_id' => $notifiableId,
                'tokens' => $fcmTokens,
                'token_count' => count($fcmTokens),
            ]);
        }

        return (new FcmMessage(notification: new FcmNotification(
            title: $this->title,
            body: $this->body,
        )))
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
                        'analytics_label' => 'freshleaf_test',
                    ],
                ],
            ]);
    }
}
