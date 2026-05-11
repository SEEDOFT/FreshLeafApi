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
        $statusName = $order->status->name ?? 'updated';

        parent::__construct(
            title: 'Order Status Updated',
            body: "Your order #{$order->order_number} status has been updated to: {$statusName}.",
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
