<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Notifications\Order\NewOrderNotification;
use App\Notifications\Order\OrderStatusUpdatedNotification;
use Illuminate\Support\Carbon;

class OrderObserver
{
    /**
     * Handle the Order "saving" event.
     */
    public function saving(Order $order): void
    {
        if ($order->isDirty('order_status_id') || $order->isClean('order_status_id') && ! $order->exists) {
            $now = Carbon::now();
            match ($order->order_status_id) {
                OrderStatus::PENDING_ID => $order->order_pending_date ??= $now,
                OrderStatus::CONFIRMED_ID => $order->order_confirmed_date ??= $now,
                OrderStatus::PREPARING_ID => $order->order_preparing_date ??= $now,
                OrderStatus::DELIVERED_ID => $order->order_delivered_date ??= $now,
                OrderStatus::CANCELLED_ID => $order->order_cancelled_date ??= $now,
                OrderStatus::AWAITING_PAYMENT_ID => $order->order_awaiting_payment_date ??= $now,
                default => null,
            };
        }
    }

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
