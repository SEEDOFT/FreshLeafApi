<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethodType;
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

                $grandTotalWalletCurrency = '0.00';
                $adminWallet = Wallet::where('user_id', 1)->where('currency_id', $wallet->currency_id)->first();
                $totalProfit = '0.00';

                foreach ($orders as $order) {
                    if ($order->payment_status_id === PaymentStatus::COMPLETED_ID) {
                        abort(422, __('api.order.already_paid'));
                    }

                    $orderAmountInWalletCurrency = '0.00';
                    $profitAmount = '0.00';
                    $exchangeRateApplied = null;

                    if ($order->currency_id === $wallet->currency_id) {
                        $orderAmountInWalletCurrency = (string) $order->total_amount;
                    } else {
                        $orderCurrencyId = (int) $order->currency_id;
                        $walletCurrencyId = (int) $wallet->currency_id;

                        // Gross amount user pays
                        $orderAmountInWalletCurrency = MoneyService::convert((string) $order->total_amount, $orderCurrencyId, $walletCurrencyId);
                        $exchangeRateApplied = ExchangeRate::getRate($orderCurrencyId, $walletCurrencyId);

                        // Base cost using inverse rate
                        $inverseRate = ExchangeRate::getRate($walletCurrencyId, $orderCurrencyId);
                        $baseCost = MoneyService::div((string) $order->total_amount, $inverseRate);

                        $profitAmount = MoneyService::sub($orderAmountInWalletCurrency, $baseCost);
                        $totalProfit = MoneyService::add($totalProfit, $profitAmount);
                    }

                    $order->update([
                        'payment_currency_id' => $wallet->currency_id,
                        'exchange_rate_applied' => $exchangeRateApplied,
                        'exchange_profit_amount' => $profitAmount,
                    ]);

                    $grandTotalWalletCurrency = MoneyService::add($grandTotalWalletCurrency, $orderAmountInWalletCurrency);
                }

                if (MoneyService::compare((string) $wallet->balance, $grandTotalWalletCurrency) < 0) {
                    abort(422, __('api.order.insufficient_balance'));
                }

                $newBalance = MoneyService::sub((string) $wallet->balance, $grandTotalWalletCurrency);

                // Deduct from wallet
                $wallet->update(['balance' => $newBalance]);

                // Create transaction
                $transaction = $wallet->transactions()->create([
                    'currency_id' => $wallet->currency_id,
                    'wallet_transaction_type_id' => WalletTransactionType::PAYMENT_ID,
                    'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                    'amount' => $grandTotalWalletCurrency,
                    'reference_id' => 0,
                    'reference_type' => 'App\\Models\\Order', // generic
                    'description' => 'Payment for '.$orders->count().' orders',
                    'transaction_date' => Carbon::now(),
                ]);

                // Add Profit to Admin Wallet if any
                if (MoneyService::compare($totalProfit, '0.00') > 0 && $adminWallet) {
                    $adminWallet->update([
                        'balance' => MoneyService::add((string) $adminWallet->balance, $totalProfit),
                    ]);

                    $adminWallet->transactions()->create([
                        'currency_id' => $adminWallet->currency_id,
                        'wallet_transaction_type_id' => WalletTransactionType::PAYMENT_ID, // Treat as generic payment for now
                        'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                        'amount' => $totalProfit,
                        'reference_id' => $transaction->id,
                        'reference_type' => 'App\\Models\\WalletTransaction',
                        'description' => 'Exchange rate profit from user wallet transaction '.$transaction->id,
                        'transaction_date' => Carbon::now(),
                    ]);
                }

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

    /**
     * Auto-cancel an order and refund if it was paid.
     *
     * @throws \Exception
     */
    public function autoCancelOrder(Order $order, string $cancellationReason = 'Auto-cancelled due to vendor unresponsiveness.'): void
    {
        try {
            DB::transaction(function () use ($order, $cancellationReason) {
                // Cancel the order
                $order->update([
                    'order_status_id' => OrderStatus::CANCELLED_ID,
                    'order_cancelled_date' => Carbon::now(),
                    'cancellation_reason' => $cancellationReason,
                ]);

                $order->histories()->create([
                    'order_status_id' => OrderStatus::CANCELLED_ID,
                    'notes' => $cancellationReason,
                ]);

                // Refund payment if completed
                if ($order->payment_status_id === PaymentStatus::COMPLETED_ID) {
                    $payment = $order->payments()->first();
                    if ($payment) {
                        $paymentMethod = $payment->paymentMethod;
                        // If it's Wallet, refund back to wallet
                        if ($paymentMethod && $paymentMethod->payment_method_type_id === PaymentMethodType::WALLET_ID) {
                            $user = $order->user;
                            $wallet = $user->wallets()->where('currency_id', $payment->currency_id)->first();

                            if ($wallet) {
                                $refundAmount = $payment->amount;
                                $newBalance = MoneyService::add((string) $wallet->balance, (string) $refundAmount);

                                $wallet->update(['balance' => $newBalance]);

                                $transaction = $wallet->transactions()->create([
                                    'currency_id' => $wallet->currency_id,
                                    'wallet_transaction_type_id' => WalletTransactionType::REFUND_ID,
                                    'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                                    'amount' => $refundAmount,
                                    'reference_id' => $order->id,
                                    'reference_type' => Order::class,
                                    'description' => 'Refund for auto-cancelled order #'.$order->id,
                                    'transaction_date' => Carbon::now(),
                                ]);

                                $transaction->recordHistory();

                                $wallet->histories()->create([
                                    'user_id' => $user->id,
                                    'currency_id' => $wallet->currency_id,
                                    'balance' => $newBalance,
                                ]);
                            }
                        }

                        $payment->update([
                            'status_id' => PaymentStatus::REFUNDED_ID,
                        ]);

                        $payment->histories()->create([
                            'payment_status_id' => PaymentStatus::REFUNDED_ID,
                            'notes' => 'Refunded due to order auto-cancellation.',
                        ]);
                    }

                    $order->update([
                        'payment_status_id' => PaymentStatus::REFUNDED_ID,
                    ]);
                }
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
