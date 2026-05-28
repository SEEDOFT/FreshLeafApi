<?php

declare(strict_types=1);

namespace App\Notifications\Order;

use App\Models\Order;
use App\Notifications\PushNotification;
use NotificationChannels\Fcm\FcmMessage;

class NewOrderNotification extends PushNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(public Order $order)
    {
        parent::__construct(
            title: __('api.notifications.new_order_title'),
            body: __('api.notifications.new_order_body', ['order_number' => $order->order_number]),
            data: [
                'type' => 'new_order',
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
