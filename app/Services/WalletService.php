<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Pagination\Paginator;

class WalletService
{
    /**
     * Get user wallets.
     *
     * @return Paginator<int, Wallet>
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
     *
     * @return Paginator<int, WalletTransaction>
     */
    public function getWalletHistory(Wallet $wallet, int $perPage): Paginator
    {
        return $wallet->transactions()
            ->orderByDesc('id')
            ->simplePaginate($perPage);
    }
}
