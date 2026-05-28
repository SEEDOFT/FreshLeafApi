<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Models\Currency;
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
            'default' => 3,
        ];
    }

    #[Override]
    protected function getStats(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [];
        }

        $productCount = VendorInventory::where('vendor_id', $user->id)->count();

        $todayOrders = Order::query()
            ->whereHas(
                'items.vendorInventory',
                static fn (Builder $q) => $q->where('vendor_id', $user->id)
            )
            ->whereDate('created_at', Carbon::now())
            ->count();

        $walletBalance = (float) (Wallet::where('user_id', $user->id)
            ->whereHas('currency', static fn (Builder $q) => $q->where('id', Currency::USD_ID))
            ->value('balance') ?? 0);

        return [
            Stat::make('My Products', $productCount)
                ->description(__('vendor.widgets.total_products_desc'))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('info'),
            Stat::make('Orders Today', $todayOrders)
                ->description(__('vendor.widgets.orders_today_desc'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make('Wallet Balance', Number::currency($walletBalance, 'USD'))
                ->description(__('vendor.widgets.wallet_earnings_desc'))
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),
        ];
    }
}
