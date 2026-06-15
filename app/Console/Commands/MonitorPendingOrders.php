<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Notifications\Order\OrderStatusUpdatedNotification;
use App\Notifications\Vendor\NewOrderAlertNotification;
use App\Services\OrderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

#[Signature('app:monitor-pending-orders')]
#[Description('Monitor pending orders and auto-cancel if not accepted within 5 minutes, also notify vendor every minute.')]
class MonitorPendingOrders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService)
    {
        /** @var Collection<int, Order> $pendingOrders */
        $pendingOrders = Order::with(['vendor', 'user', 'payments.paymentMethod', 'histories'])
            ->where('order_status_id', OrderStatus::PENDING_ID)
            ->whereNotNull('order_pending_date')
            ->get();

        foreach ($pendingOrders as $order) {
            $minutesPending = (int) abs($order->order_pending_date->diffInMinutes(Carbon::now()));

            if ($minutesPending >= 5) {
                // Auto-cancel and refund
                $orderService->autoCancelOrder($order);

                // Send notification to consumer
                if ($order->user) {
                    $order->user->notify(new OrderStatusUpdatedNotification($order));
                }

                // Send notification to vendor that order was cancelled
                if ($order->vendor) {
                    $order->vendor->notify(new OrderStatusUpdatedNotification($order));
                }
            } elseif ($minutesPending > 0) {
                // Send alert every minute
                if ($order->vendor) {
                    $order->vendor->notify(new NewOrderAlertNotification($order));
                }
            }
        }
    }
}
