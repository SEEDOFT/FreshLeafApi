<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\VendorOrderUpdated;
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
        if ($order->order_status_id !== OrderStatus::AWAITING_PAYMENT_ID) {
            $order->user?->notify(new NewOrderNotification($order));

            if ($order->order_status_id === OrderStatus::PENDING_ID) {
                $this->notifyVendor($order);
            }
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('order_status_id')) {
            $originalStatus = (int) $order->getOriginal('order_status_id');
            $newStatus = (int) $order->order_status_id;

            if (
                $originalStatus === OrderStatus::AWAITING_PAYMENT_ID
                && $newStatus === OrderStatus::PENDING_ID
            ) {
                $order->user?->notify(new NewOrderNotification($order));
                $this->notifyVendor($order);
            } else {
                $order->user?->notify(new OrderStatusUpdatedNotification($order));
            }
        }
    }

    /**
     * Notify vendor of new order.
     */
    private function notifyVendor(Order $order): void
    {
        $vendor = $order->items()->first()?->vendorInventory?->vendor;
        if ($vendor) {
            broadcast(
                new VendorOrderUpdated($vendor->id, $order->id, $order->order_number)
            )->toOthers();
        }
    }
}
