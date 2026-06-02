<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderService
{
    /**
     * Pay for multiple orders using the user's wallet.
     *
     * @param  Collection<int, Order>  $orders
     *
     * @throws HttpException
     * @throws RuntimeException
     */
    public function batchPayWithWallet(User $user, Collection $orders, Wallet $wallet): void
    {
        try {
            DB::transaction(function () use ($user, $orders, $wallet) {
                if ($wallet->user_id !== $user->id) {
                    abort(403, __('api.order.wallet_unauthorized'));
                }

                $grandTotal = '0.00';
                foreach ($orders as $order) {
                    if ($order->payment_status_id === PaymentStatus::COMPLETED_ID) {
                        abort(422, __('api.order.already_paid'));
                    }
                    $grandTotal = MoneyService::add($grandTotal, (string) $order->total_amount);
                }

                if (MoneyService::compare((string) $wallet->balance, $grandTotal) < 0) {
                    abort(422, __('api.order.insufficient_balance'));
                }

                $newBalance = MoneyService::sub((string) $wallet->balance, $grandTotal);

                // Deduct from wallet
                $wallet->update(['balance' => $newBalance]);

                // Create transaction
                $transaction = $wallet->transactions()->create([
                    'currency_id' => $wallet->currency_id,
                    'wallet_transaction_type_id' => WalletTransactionType::PAYMENT_ID,
                    'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                    'amount' => $grandTotal,
                    'reference_id' => 0,
                    'reference_type' => 'App\\Models\\Order', // generic
                    'description' => 'Payment for '.$orders->count().' orders',
                    'transaction_date' => Carbon::now(),
                ]);

                // Create transaction history
                $transaction->recordHistory();

                // Create wallet history
                $wallet->histories()->create([
                    'user_id' => $user->id,
                    'currency_id' => $wallet->currency_id,
                    'balance' => $newBalance,
                ]);

                // Update payment and order status for each order
                foreach ($orders as $order) {
                    $order->update([
                        'order_status_id' => OrderStatus::PENDING_ID,
                        'payment_status_id' => PaymentStatus::COMPLETED_ID,
                    ]);

                    $payment = $order->payments()->first();
                    if ($payment) {
                        $payment->update([
                            'status_id' => PaymentStatus::COMPLETED_ID,
                        ]);
                        $payment->histories()->create([
                            'payment_status_id' => PaymentStatus::COMPLETED_ID,
                            'notes' => 'Paid via Wallet ID: '.$wallet->id,
                        ]);
                    }

                    $order->histories()->create([
                        'order_status_id' => OrderStatus::PENDING_ID,
                        'notes' => 'Payment received via Wallet.',
                    ]);
                }
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            abort(422, __('api.order.payment_failed').$e->getMessage());
        }
    }

    /**
     * Pay for an order using the user's wallet.
     *
     *
     * @throws HttpException
     * @throws RuntimeException
     */
    public function payWithWallet(User $user, Order $order, Wallet $wallet): void
    {
        try {
            DB::transaction(function () use ($user, $order, $wallet) {
                if ($wallet->user_id !== $user->id) {
                    abort(403, __('api.order.wallet_unauthorized'));
                }

                if ($order->payment_status_id === PaymentStatus::COMPLETED_ID) {
                    abort(422, __('api.order.already_paid'));
                }

                $orderTotal = $order->total_amount;

                if (MoneyService::compare((string) $wallet->balance, (string) $orderTotal) < 0) {
                    abort(422, __('api.order.insufficient_balance'));
                }

                $newBalance = MoneyService::sub((string) $wallet->balance, (string) $orderTotal);

                // Deduct from wallet
                $wallet->update(['balance' => $newBalance]);

                // Create transaction
                $transaction = $wallet->transactions()->create([
                    'currency_id' => $wallet->currency_id,
                    'wallet_transaction_type_id' => WalletTransactionType::PAYMENT_ID,
                    'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                    'amount' => $orderTotal,
                    'reference_id' => $order->id,
                    'reference_type' => Order::class,
                    'description' => 'Payment for order #'.$order->id,
                    'transaction_date' => Carbon::now(),
                ]);

                // Create transaction history
                $transaction->recordHistory();

                // Create wallet history
                $wallet->histories()->create([
                    'user_id' => $user->id,
                    'currency_id' => $wallet->currency_id,
                    'balance' => $newBalance,
                ]);

                // Update payment and order status
                $order->update([
                    'order_status_id' => OrderStatus::PENDING_ID,
                    'payment_status_id' => PaymentStatus::COMPLETED_ID,
                ]);

                // Update payments related to this order
                $payment = $order->payments()->first();
                if ($payment) {
                    $payment->update([
                        'status_id' => PaymentStatus::COMPLETED_ID,
                    ]);
                    $payment->histories()->create([
                        'payment_status_id' => PaymentStatus::COMPLETED_ID,
                        'notes' => 'Paid via Wallet ID: '.$wallet->id,
                    ]);
                }

                $order->histories()->create([
                    'order_status_id' => OrderStatus::PENDING_ID,
                    'notes' => 'Payment received via Wallet.',
                ]);
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            abort(422, __('api.order.payment_failed').$e->getMessage());
        }
    }
}
