<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransactionHistory;
use Illuminate\Contracts\Pagination\Paginator;

class WalletService
{
    /**
     * Get user wallets.
     * @return Paginator<Wallet>
     */
    public function getUserWallets(User $user, int $perPage): Paginator
    {
        return $user->wallets()
            ->with('currency')
            ->orderByDesc('id')
            ->simplePaginate($perPage);
    }

    /**
     * Get wallet history.
     * @return Paginator<WalletTransactionHistory>
     */
    public function getWalletHistory(Wallet $wallet, int $perPage): Paginator
    {
        return $wallet->histories()
            ->with('currency')
            ->orderByDesc('id')
            ->simplePaginate($perPage);
    }
}
