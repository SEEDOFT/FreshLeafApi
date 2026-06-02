<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\VendorInventory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Override;

class VendorPlatformActivity extends BaseWidget
{
    protected static ?int $sort = 2;

    #[Override]
    protected string $view = 'filament.widgets.stats-overview';

    public function getHeading(): ?string
    {
        return 'PLATFORM ACTIVITY';
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

        return [
            Stat::make(__('vendor.widgets.my_products'), (string) $productCount)
                ->description(__('vendor.widgets.total_products_desc'))
                ->descriptionIcon('heroicon-o-cube')
                ->color('danger'), // Pink
            Stat::make(__('vendor.widgets.orders_today'), (string) $todayOrders)
                ->description(__('vendor.widgets.orders_today_desc'))
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('success'),
        ];
    }
}
