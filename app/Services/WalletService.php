<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletService
{
    /**
     * Default relationships loaded for wallet resources.
     *
     * @var list<string>
     */
    private const DEFAULT_RELATIONS = ['currency'];

    /**
     * Get user wallets with default relationships.
     *
     * @return LengthAwarePaginator<int, Wallet>
     */
    public function getUserWallets(User $user, int $perPage): LengthAwarePaginator
    {
        return $user->wallets()
            ->with(self::DEFAULT_RELATIONS)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get paginated wallet history.
     *
     * @return LengthAwarePaginator<int, WalletHistory>
     */
    public function getWalletHistory(Wallet $wallet, int $perPage): LengthAwarePaginator
    {
        return $wallet->histories()
            ->with(self::DEFAULT_RELATIONS)
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
