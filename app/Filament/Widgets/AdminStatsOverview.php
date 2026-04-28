<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorProfile;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Override;

class AdminStatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected string $view = 'filament.widgets.admin-stats-overview';

    #[Override]
    protected function getStats(): array
    {
        $totalRevenue = (float) Order::whereHas('paymentStatus', static fn ($query) => $query->where('code', 'paid'))
            ->sum('total_amount');

        $consumerCount = User::where('user_type_id', UserType::USER)
            ->where('user_status_id', UserStatus::ACTIVE)
            ->count();
        $vendorCount = User::where('user_type_id', UserType::VENDOR)
            ->where('user_status_id', UserStatus::ACTIVE)
            ->count();
        $pendingVendors = VendorProfile::where('is_verified', false)
            ->count();

        return [
            Stat::make(__('admin.resources.product.revenue') ?: 'Total Revenue', Number::currency($totalRevenue, 'USD'))
                ->description(__('admin.resources.order.paid_description') ?: 'Total from paid orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make(__('admin.resources.user.plural_label'), $consumerCount)
                ->description(__('admin.resources.user.registered_description') ?: 'Total registered consumers')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make(__('admin.resources.vendor.plural_label'), $vendorCount)
                ->description($pendingVendors.' '.(__('admin.resources.vendor.pending_approval') ?: 'pending approval'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color($pendingVendors > 0 ? 'warning' : 'success'),
        ];
    }

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
}
