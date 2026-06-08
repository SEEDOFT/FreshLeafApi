<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethodType;
use App\Models\PaymentStatus;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VendorPayoutService
{
    /**
     * Process a payout for a completed, unpaid order.
     * Returns true if successful, false if it skipped or failed.
     */
    public function payoutOrder(Order $order): bool
    {
        if ($order->order_status_id !== OrderStatus::DELIVERED_ID) {
            return false;
        }

        if ($order->is_vendor_paid) {
            return false;
        }

        // Must have completed payment
        if ($order->payment_status_id !== PaymentStatus::COMPLETED_ID) {
            return false;
        }

        // Check if the order was COD. If it is COD, we exclude it from digital payout,
        // but mark it as paid so we don't try again.
        $isCod = $order->payments()
            ->whereHas(
                'paymentMethod',
                fn ($q) => $q->where('payment_method_type_id', PaymentMethodType::COD_ID)
            )->exists();

        if ($isCod) {
            // Vendor collected cash directly. We do not transfer digital funds.
            $order->update(['is_vendor_paid' => true]);

            return true;
        }

        // Calculate the total net amount for the vendor for this order
        $vendorNetAmount = $order->items()->sum('vendor_net_amount');

        if ($vendorNetAmount <= 0) {
            $order->update(['is_vendor_paid' => true]);

            return true;
        }

        // Execute the financial transaction
        DB::transaction(function () use ($order, $vendorNetAmount) {
            $vendorId = $order->vendor_id;

            // Find or create the USD wallet for the vendor (assuming USD as base for earnings)
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $vendorId, 'currency_id' => Currency::USD_ID],
                ['balance' => '0.00']
            );

            // Add balance
            $newBalance = MoneyService::add((string) $wallet->balance, (string) $vendorNetAmount);
            $wallet->update(['balance' => $newBalance]);

            // Track wallet history
            $wallet->histories()->create([
                'user_id' => $wallet->user_id,
                'currency_id' => $wallet->currency_id,
                'balance' => $newBalance,
            ]);

            // Create Deposit transaction for the vendor
            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'currency_id' => $wallet->currency_id,
                'wallet_transaction_type_id' => WalletTransactionType::DEPOSIT_ID,
                'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                'amount' => $vendorNetAmount,
                'reference_id' => $order->id,
                'reference_type' => Order::class,
                'description' => 'Payout for Order #'.$order->order_number,
                'transaction_date' => Carbon::now(),
            ]);

            $transaction->recordHistory();

            // Mark order as paid
            $order->update([
                'is_vendor_paid' => true,
                'vendor_payout_transaction_id' => $transaction->id,
            ]);

            // Calculate total commission for the platform
            $adminCommissionAmount = $order->items()->sum('commission_amount');

            if ($adminCommissionAmount > 0) {
                // Find primary admin to receive commission
                $admin = \App\Models\User::where('user_type_id', \App\Models\UserType::ADMIN_ID)->first();
                if ($admin) {
                    $adminWallet = Wallet::firstOrCreate(
                        ['user_id' => $admin->id, 'currency_id' => Currency::USD_ID],
                        ['balance' => '0.00']
                    );

                    $newAdminBalance = MoneyService::add((string) $adminWallet->balance, (string) $adminCommissionAmount);
                    $adminWallet->update(['balance' => $newAdminBalance]);

                    $adminWallet->histories()->create([
                        'user_id' => $adminWallet->user_id,
                        'currency_id' => $adminWallet->currency_id,
                        'balance' => $newAdminBalance,
                    ]);

                    $adminTransaction = WalletTransaction::create([
                        'wallet_id' => $adminWallet->id,
                        'currency_id' => $adminWallet->currency_id,
                        'wallet_transaction_type_id' => WalletTransactionType::DEPOSIT_ID,
                        'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
                        'amount' => $adminCommissionAmount,
                        'reference_id' => $order->id,
                        'reference_type' => Order::class,
                        'description' => 'Commission for Order #'.$order->order_number,
                        'transaction_date' => Carbon::now(),
                    ]);

                    $adminTransaction->recordHistory();
                }
            }
        });

        return true;
    }
}
