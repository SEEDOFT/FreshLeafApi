<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Pages;

use App\Filament\Admin\Resources\Wallets\WalletResource;
use App\Models\Currency;
use App\Models\Order;
use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use Filament\Resources\Pages\Page;
use Override;

class ListWallets extends Page
{
    #[Override]
    protected static string $resource = WalletResource::class;

    #[Override]
    protected string $view = 'filament.admin.pages.wallets.list-wallets';

    public ?int $userTypeFilter = null;

    #[Override]
    public function getTitle(): string
    {
        return __('admin.resources.wallet.plural_label');
    }

    /**
     * @return array<int, string>
     */
    public function getUserTypeFilterOptions(): array
    {
        return [
            0 => __('admin.resources.wallet.filter_all'),
            UserType::ADMIN_ID => __('admin.resources.wallet.filter_admin'),
            UserType::VENDOR_ID => __('admin.resources.wallet.filter_vendor'),
            UserType::CONSUMER_ID => __('admin.resources.wallet.filter_consumer'),
        ];
    }

    public function filterByUserType(int $typeId): void
    {
        $this->userTypeFilter = $typeId === 0 ? null : $typeId;
    }

    #[Override]
    protected function getViewData(): array
    {
        $walletQuery = Wallet::with('currency')
            ->orderBy('user_id');

        if ($this->userTypeFilter !== null) {
            $relation = match ($this->userTypeFilter) {
                UserType::ADMIN_ID => 'admin',
                UserType::VENDOR_ID => 'vendor',
                UserType::CONSUMER_ID => 'user',
                default => null,
            };

            if ($relation !== null) {
                $walletQuery->whereHas($relation);
            }
        }

        $groupedData = [];

        foreach ($walletQuery->get() as $wallet) {
            $owner = User::find($wallet->user_id);

            if ($owner === null) {
                continue;
            }

            $ownerKey = $wallet->user_id;

            if (! isset($groupedData[$ownerKey])) {
                $groupedData[$ownerKey] = [
                    'owner' => $owner,
                    'wallets' => [],
                    'walletTransactions' => [],
                ];
            }

            $groupedData[$ownerKey]['wallets'][] = $wallet;
        }

        foreach ($groupedData as $ownerKey => $group) {
            $walletTransactions = [];
            foreach ($group['wallets'] as $wallet) {
                $walletTransactions[$wallet->id] = $wallet->transactions()
                    ->with(['type', 'status', 'currency'])
                    ->latest()
                    ->limit(10)
                    ->get();
            }
            $groupedData[$ownerKey]['walletTransactions'] = $walletTransactions;
        }

        return [
            'groupedData' => array_values($groupedData),
            'formatWalletBalance' => static function (float $balance, ?Currency $currency): string {
                return Order::formatMoney($balance, $currency);
            },
        ];
    }
}
