<?php

declare(strict_types=1);

namespace App\Notifications\Order;

use App\Models\Order;
use App\Notifications\PushNotification;
use NotificationChannels\Fcm\FcmMessage;

class OrderStatusUpdatedNotification extends PushNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(public Order $order)
    {
        $statusName = $order->status->name ?? __('api.order.status_updated');

        parent::__construct(
            title: __('api.notifications.order_status_updated_title'),
            body: __('api.notifications.order_status_updated_body', ['order_number' => $order->order_number, 'status' => $statusName]),
            data: [
                'type' => 'order_status_update',
                'order_id' => (string) $order->id,
                'route' => '/order_detail',
                'arguments' => json_encode(['id' => $order->id]),
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
