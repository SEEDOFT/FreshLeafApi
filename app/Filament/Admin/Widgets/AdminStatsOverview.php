<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\PaymentStatus;
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
    #[Override]
    protected ?string $pollingInterval = null;

    #[Override]
    protected string $view = 'filament.widgets.stats-overview';

    protected int|string|array $columnSpan = 'full';

    #[Override]
    protected function getStats(): array
    {
        $totalRevenue = (float) Order::query()
            ->whereHas('paymentStatus', static fn ($query) => $query->where('id', PaymentStatus::COMPLETED_ID))
            ->sum('total_amount');

        $consumerCount = User::where('user_type_id', UserType::CONSUMER_ID)
            ->where('user_status_id', UserStatus::ACTIVE_ID)
            ->count();
        $vendorCount = User::where('user_type_id', UserType::VENDOR_ID)
            ->where('user_status_id', UserStatus::ACTIVE_ID)
            ->count();
        $pendingVendors = VendorProfile::where('is_verified', false)
            ->count();

        return [
            Stat::make(__('admin.resources.product.revenue'), Number::currency($totalRevenue, 'USD'))
                ->description(__('admin.resources.order.paid_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make(__('admin.resources.user.plural_label'), $consumerCount)
                ->description(__('admin.resources.user.registered_description'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make(__('admin.resources.vendor.plural_label'), $vendorCount)
                ->description($pendingVendors.' '.__('admin.resources.vendor.pending_approval'))
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
