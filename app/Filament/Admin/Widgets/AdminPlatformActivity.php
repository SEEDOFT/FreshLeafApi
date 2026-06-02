<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorInventory;
use App\Models\VendorProfile;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

class AdminPlatformActivity extends BaseWidget
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
        return 3;
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
        $consumerCount = User::where('user_type_id', UserType::CONSUMER_ID)
            ->where('user_status_id', UserStatus::ACTIVE_ID)
            ->count();

        $vendorCount = User::where('user_type_id', UserType::VENDOR_ID)
            ->where('user_status_id', UserStatus::ACTIVE_ID)
            ->count();

        $pendingVendors = VendorProfile::where('is_verified', false)->count();

        $productsListed = VendorInventory::count();

        return [
            Stat::make('Users', (string) $consumerCount)
                ->description('Registered consumers')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),
            Stat::make('Vendors', (string) $vendorCount)
                ->description($pendingVendors > 0 ? "{$pendingVendors} pending approval" : 'All approved')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color($pendingVendors > 0 ? 'success' : 'gray'),
            Stat::make('Products listed', (string) $productsListed)
                ->description('Across all vendors')
                ->descriptionIcon('heroicon-o-cube')
                ->color('danger'),
        ];
    }
}
