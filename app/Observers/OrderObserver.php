<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\VendorOrderUpdated;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use App\Notifications\Order\NewOrderNotification;
use App\Notifications\Order\OrderStatusUpdatedNotification;
use App\Services\MoneyService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use function get_class;

class OrderObserver
{
    /**
     * Handle the Order "saving" event.
     */
    public function saving(Order $order): void
    {
        if (
            $order->isDirty('order_status_id') ||
            $order->isClean('order_status_id') &&
            ! $order->exists
        ) {
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

            if ($newStatus === OrderStatus::CANCELLED_ID) {
                $this->handleOrderCancellation($order);
            }
        }
    }

    /**
     * Handle refunding, returning stock, and updating records when an order is cancelled.
     */
    private function handleOrderCancellation(Order $order): void
    {
        $order->loadMissing(['payment', 'items.vendorInventory', 'user']);

        DB::transaction(function () use ($order) {
            // 1. Handle Payment and Refund
            if ($payment = $order->payment) {
                if ($payment->status_id === PaymentStatus::COMPLETED_ID) {
                    $payment->update([
                        'status_id' => PaymentStatus::REFUNDED_ID,
                    ]);
                    $payment->histories()->create([
                        'payment_status_id' => PaymentStatus::REFUNDED_ID,
                        'notes' => 'Refund processed due to order cancellation.',
                    ]);

                    if ($order->user_id && $payment->currency_id) {
                        $wallet = Wallet::where('user_id', $order->user_id)
                            ->where('currency_id', $payment->currency_id)
                            ->first();

                        if (! $wallet) {
                            return;
                        }

                        $amount = (string) $payment->amount;
                        $newBalance = MoneyService::add((string) $wallet->balance, $amount);
                        $wallet->update(['balance' => $newBalance]);

                        $wallet->histories()->create([
                            'user_id' => $wallet->user_id,
                            'currency_id' => $wallet->currency_id,
                            'balance' => $newBalance,
                        ]);

                        $transaction = WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'wallet_transaction_type_id' => WalletTransactionType::REFUND_ID,
                            'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                            'amount' => $amount,
                            'payment_method_id' => $payment->payment_method_id,
                            'reference_id' => $order->id,
                            'reference_type' => get_class($order),
                            'description' => 'Refund for cancelled order #'.$order->order_number,
                            'transaction_date' => Carbon::now(),
                        ]);

                        $transaction->recordHistory();
                    }
                } elseif ($payment->status_id === PaymentStatus::PENDING_ID) {
                    $payment->update(['status_id' => PaymentStatus::FAILED_ID]);
                    $payment->histories()->create([
                        'payment_status_id' => PaymentStatus::FAILED_ID,
                        'notes' => 'Payment failed because the order was cancelled.',
                    ]);
                }
            }

            // 2. Handle Stock Return
            foreach ($order->items as $item) {
                if ($inventory = $item->vendorInventory) {
                    $newQuantity = MoneyService::add(
                        (string) $inventory->stock_quantity,
                        (string) $item->quantity
                    );
                    $inventory->update(['stock_quantity' => $newQuantity]);

                    $inventory->histories()->create([
                        'quantity_change' => '+'.MoneyService::quantity($item->quantity),
                        'new_quantity' => $newQuantity,
                        'reference_type' => get_class($item),
                        'reference_id' => $item->id,
                        'reason' => 'Order Cancellation Return',
                    ]);
                }
            }
        });
    }

    /**
     * Notify vendor of new order.
     */
    private function notifyVendor(Order $order): void
    {
        $vendor = $order->items()->first()?->vendorInventory?->vendor;

        if ($vendor) {
            broadcast(
                new VendorOrderUpdated(
                    $vendor->id,
                    $order->id,
                    $order->order_number,
                )
            )->toOthers();
        }
    }
}
