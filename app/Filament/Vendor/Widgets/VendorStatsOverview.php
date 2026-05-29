<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Models\CommissionFee;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorInventory;
use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Override;

class VendorStatsOverview extends BaseWidget
{
    #[Override]
    protected string $view = 'filament.widgets.stats-overview';

    #[Override]
    protected int|string|array $columnSpan = 'full';

    #[Override]
    public function getViewData(): array
    {
        return [
            'stats' => $this->getStats(),
            'columns' => $this->getColumns(),
        ];
    }

    #[Override]
    protected function getColumns(): int|array|null
    {
        return [
            'default' => 1,
            'md' => 2,
            'lg' => 3,
        ];
    }

    #[Override]
    protected function getStats(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [];
        }

        $productCount = VendorInventory::query()
            ->where('vendor_id', $user->id)
            ->count();

        $todayOrders = Order::query()
            ->whereHas(
                'items.vendorInventory',
                static fn (Builder $query): Builder => $query->where('vendor_id', $user->id)
            )
            ->whereDate('created_at', Carbon::now())
            ->count();

        $walletBalance = (float) (Wallet::query()
            ->where('user_id', $user->id)
            ->whereHas(
                'currency',
                static fn (Builder $query): Builder => $query->where('id', Currency::USD_ID)
            )
            ->value('balance') ?? 0
        );

        $usdToKhr = (float) ExchangeRate::getRate(Currency::USD_ID, Currency::KHR_ID);
        $khrToUsd = (float) ExchangeRate::getRate(Currency::KHR_ID, Currency::USD_ID);
        $commission = CommissionFee::latest()->first();

        return [
            Stat::make(__('vendor.widgets.my_products'), $productCount)
                ->description(__('vendor.widgets.total_products_desc'))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('info'),
            Stat::make(__('vendor.widgets.orders_today'), $todayOrders)
                ->description(__('vendor.widgets.orders_today_desc'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make(__('vendor.widgets.wallet_balance'), Number::currency($walletBalance, 'USD'))
                ->description(__('vendor.widgets.wallet_earnings_desc'))
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),
            Stat::make(
                __('vendor.widgets.platform_commission'),
                $commission
                    ? "{$commission->rate}%"
                    : __('vendor.widgets.not_available')
            )
                ->description($commission->description ?? __('vendor.widgets.commission_fee_desc'))
                ->descriptionIcon('heroicon-o-receipt-percent')
                ->color('info'),
            Stat::make(
                __('vendor.widgets.usd_to_khr_label'),
                __('vendor.widgets.usd_to_khr', ['rate' => number_format($usdToKhr, 0)]),
            )
                ->description(__('vendor.widgets.usd_to_khr_desc'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            Stat::make(
                __('vendor.widgets.khr_to_usd_label'),
                __('vendor.widgets.khr_to_usd', ['rate' => number_format($khrToUsd, 4)]),
            )
                ->description(__('vendor.widgets.khr_to_usd_desc'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('info'),
        ];
    }
}
