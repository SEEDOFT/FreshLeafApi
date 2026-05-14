<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use App\Notifications\Order\NewOrderNotification;
use App\Notifications\Order\OrderStatusUpdatedNotification;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Notify the user who placed the order
        $order->user?->notify(new NewOrderNotification($order));
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('order_status_id')) {
            $order->user?->notify(new OrderStatusUpdatedNotification($order));
        }
    }
}
