<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\CommissionFee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Override;

class AdminFinancialOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    #[Override]
    protected string $view = 'filament.widgets.stats-overview';

    public function getHeading(): ?string
    {
        return 'FINANCIAL OVERVIEW';
    }

    #[Override]
    protected function getColumns(): int
    {
        return 2;
    }

    #[Override]
    public function getViewData(): array
    {
        return [
            'stats' => $this->getStats(),
            'columns' => $this->getColumns(),
            'heading' => $this->getHeading(),
        ];
    }

    #[Override]
    protected function getStats(): array
    {
        $totalCommission = (float) OrderItem::sum('commission_amount');
        $vendorNetTotal = (float) OrderItem::sum('vendor_net_amount');

        $totalRevenue = (float) Order::query()
            ->whereHas(
                'paymentStatus',
                static fn (Builder $query): Builder => $query->where('id', PaymentStatus::COMPLETED_ID)
            )
            ->sum('total_amount');

        $commission = CommissionFee::latest()->first();

        return [
            Stat::make('Total platform commission', '$'.number_format($totalCommission, 2))
                ->description('Total fees earned from vendor sales')
                ->descriptionIcon('heroicon-o-receipt-refund')
                ->color('success'),
            Stat::make('Pending vendor payouts', '$'.number_format($vendorNetTotal, 2))
                ->description('Awaiting disbursement')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->extraAttributes(['is_badge' => true]),
            Stat::make('Total revenue', '$'.number_format($totalRevenue, 2))
                ->description('From paid orders')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('info'),
            Stat::make('Commission rate', $commission ? "{$commission->rate}%" : '15.00%')
                ->description('Platform commission fee')
                ->descriptionIcon('heroicon-o-receipt-percent')
                ->color('primary'),
        ];
    }
}
