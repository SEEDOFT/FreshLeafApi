<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CancelUnpaidOrderJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $orderId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::find($this->orderId);

        if (! $order) {
            return;
        }

        if (
            $order->order_status_id === OrderStatus::AWAITING_PAYMENT_ID &&
            $order->payment_status_id === PaymentStatus::PENDING_ID
        ) {
            $order->update([
                'order_status_id' => OrderStatus::CANCELLED_ID,
            ]);

            $order->histories()->create([
                'order_status_id' => OrderStatus::CANCELLED_ID,
                'notes' => 'Order cancelled automatically due to payment timeout.',
            ]);
        }
    }
}
