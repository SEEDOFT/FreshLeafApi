<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

class AdminCommissionWidget extends BaseWidget
{
    #[Override]
    protected string $view = 'filament.widgets.admin-commission-widget';

    #[Override]
    protected function getStats(): array
    {
        $totalCommission = (float) OrderItem::sum('commission_amount');
        $vendorNetTotal = (float) OrderItem::sum('vendor_net_amount');

        return [
            Stat::make(__('admin.widgets.commission.platform_commission'), '$'.number_format($totalCommission, 2))
                ->description(__('admin.widgets.commission.platform_commission_desc'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->extraAttributes([
                    'class' => '!border-0 !shadow-none !bg-transparent',
                ]),

            Stat::make(__('admin.widgets.commission.pending_payouts'), '$'.number_format($vendorNetTotal, 2))
                ->description(__('admin.widgets.commission.pending_payouts_desc'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->extraAttributes([
                    'class' => '!border-0 !shadow-none !bg-transparent',
                ]),
        ];
    }
}
