<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use App\Filament\Vendor\Widgets\VendorEarningsChart;
use App\Filament\Vendor\Widgets\VendorStatsOverview;
use App\Filament\Widgets\CustomAccountWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Override;

class Dashboard extends BaseDashboard
{
    /**
     * @return array<class-string>
     */
    #[Override]
    public function getWidgets(): array
    {
        return [
            VendorStatsOverview::class,
            CustomAccountWidget::class,
            VendorEarningsChart::class,
        ];
    }

    /**
     * @return int | array<string, ?int>
     */
    #[Override]
    public function getColumns(): int|array
    {
        return 1;
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.dashboard');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.dashboard');
    }
}
