<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminCommissionWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCommission = OrderItem::sum('commission_amount');
        $vendorNetTotal = OrderItem::sum('vendor_net_amount');

        return [
            Stat::make('Total Platform Commission', '$' . number_format($totalCommission, 2))
                ->description('Total fees earned from vendor sales')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            
            Stat::make('Pending Vendor Payouts', '$' . number_format($vendorNetTotal, 2))
                ->description('Total net amount owed to vendors')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
