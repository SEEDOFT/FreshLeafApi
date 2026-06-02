<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WalletTransactionService
{
    /**
     * Default relationships loaded for wallet transactions.
     *
     * @var list<string>
     */
    private const DEFAULT_RELATIONS = ['type', 'status'];

    /**
     * Get paginated transactions for a user's wallet.
     *
     * @return LengthAwarePaginator<int, WalletTransaction>
     */
    public function getUserTransactions(
        User $user,
        ?int $walletId,
        int $perPage
    ): LengthAwarePaginator {
        $query = WalletTransaction::query()
            ->whereHas(
                'wallet',
                static fn (Builder $query): Builder => $query->where('user_id', $user->id)
            )
            ->with(self::DEFAULT_RELATIONS)
            ->orderByDesc('id');

        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new transaction and log history entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function createTransaction(User $user, array $data): WalletTransaction
    {
        return DB::transaction(function () use ($data) {
            // Auto-fill currency_id from the wallet if not explicitly provided
            if (! isset($data['currency_id']) && isset($data['wallet_id'])) {
                $wallet = Wallet::find($data['wallet_id']);
                if ($wallet) {
                    $data['currency_id'] = $wallet->currency_id;
                }
            }

            $transaction = WalletTransaction::create($data);

            $transaction->recordHistory();

            return $transaction;
        });
    }

    /**
     * Update an existing transaction and log history if the status changes.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTransaction(
        WalletTransaction $transaction,
        User $user,
        array $data
    ): WalletTransaction {
        return DB::transaction(function () use ($transaction, $data) {
            $transaction->update($data);

            if ($transaction->wasChanged()) {
                $transaction->recordHistory();
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
