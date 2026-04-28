<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Override;

class VendorStatsOverview extends BaseWidget
{
    #[Override]
    protected function getStats(): array
    {
        $user = Auth::user();

        $productCount = Product::where('user_id', $user->id)->count();

        $todayOrders = Order::whereHas('items.product', fn ($q) => $q->where('user_id', $user->id))
            ->whereDate('created_at', now())
            ->count();

        $walletBalance = (float) (Wallet::where('user_id', $user->id)
            ->whereHas('currency', fn ($q) => $q->where('code', 'USD'))
            ->value('balance') ?? 0);

        return [
            Stat::make('My Products', $productCount)
                ->description('Total products listed')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('info'),
            Stat::make('Orders Today', $todayOrders)
                ->description('New orders received today')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make('Wallet Balance', Number::currency($walletBalance, 'USD'))
                ->description('Current USD earnings')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),
        ];
    }
}
