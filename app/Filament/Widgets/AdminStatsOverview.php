<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\UserType;
use App\Models\VendorProfile;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Override;

class AdminStatsOverview extends BaseWidget
{
    #[Override]
    protected function getStats(): array
    {
        $totalRevenue = Order::whereHas('paymentStatus', fn ($query) => $query->where('code', 'paid'))
            ->sum('total_amount');

        $consumerCount = User::where('user_type_id', UserType::USER)->count();
        $vendorCount = User::where('user_type_id', UserType::VENDOR)->count();
        $pendingVendors = VendorProfile::where('is_verified', false)->count();

        return [
            Stat::make('Total Revenue', Number::currency($totalRevenue, 'USD'))
                ->description('Total from paid orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Customers', $consumerCount)
                ->description('Total registered consumers')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Vendors', $vendorCount)
                ->description($pendingVendors.' pending approval')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color($pendingVendors > 0 ? 'warning' : 'success'),
        ];
    }
}
