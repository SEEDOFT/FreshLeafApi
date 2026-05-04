<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionHistory;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class WalletTransactionService
{
    /**
     * Get paginated transactions for a user.
     *
     * @return Paginator<int, WalletTransaction>
     */
    public function getUserTransactions(User $user, ?int $walletId, int $perPage): Paginator
    {
        $query = WalletTransaction::whereHas('wallet', static fn ($query) => $query->where('user_id', $user->id))
            ->with(['type', 'status'])
            ->orderByDesc('id');

        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }

        return $query->simplePaginate($perPage);
    }

    /**
     * Create a new transaction with history log.
     *
     * @param  array<string, mixed>  $data
     */
    public function createTransaction(User $user, array $data): WalletTransaction
    {
        return DB::transaction(static function () use ($user, $data) {
            $transaction = WalletTransaction::create($data);

            WalletTransactionHistory::create([
                'wallet_transaction_id' => $transaction->id,
                'from_wallet_transaction_status_id' => null,
                'to_wallet_transaction_status_id' => $transaction->wallet_transaction_status_id,
                'changed_by_user_id' => $user->id,
                'note' => 'Transaction initiated',
            ]);

            return $transaction;
        });
    }

    /**
     * Update an existing transaction and log history if status changes.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTransaction(WalletTransaction $transaction, User $user, array $data): WalletTransaction
    {
        return DB::transaction(static function () use ($transaction, $user, $data) {
            $oldStatusId = $transaction->wallet_transaction_status_id;
            $transaction->update($data);

            if ($transaction->wasChanged('wallet_transaction_status_id')) {
                WalletTransactionHistory::create([
                    'wallet_transaction_id' => $transaction->id,
                    'from_wallet_transaction_status_id' => $oldStatusId,
                    'to_wallet_transaction_status_id' => $transaction->wallet_transaction_status_id,
                    'changed_by_user_id' => $user->id,
                    'note' => 'Status updated',
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Delete a transaction.
     */
    public function deleteTransaction(WalletTransaction $transaction): ?bool
    {
        return $transaction->delete();
    }
}
