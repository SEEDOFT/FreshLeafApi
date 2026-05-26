<?php

declare(strict_types=1);

namespace App\Channels;

use App\Models\Notification as NotificationModel;
use App\Models\NotificationStatus;
use App\Models\NotificationType;
use App\Notifications\PushNotification;
use Illuminate\Notifications\Notification;

class DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! $notification instanceof PushNotification) {
            return;
        }

        $typeId = NotificationType::SYSTEM_ID;
        $typeCode = $notification->data['type'] ?? '';

        if ($typeCode === 'order_status_update' || $typeCode === 'new_order') {
            $typeId = NotificationType::ORDER_UPDATE_ID;
        } elseif ($typeCode === 'promotion') {
            $typeId = NotificationType::PROMOTION_ID;
        }

        NotificationModel::create([
            'user_id' => $notifiable->id,
            'notification_type_id' => $typeId,
            'notification_status_id' => NotificationStatus::UNREAD_ID,
            'title' => $notification->title,
            'message' => $notification->body,
            'data' => $notification->data,
        ]);
    }
}
