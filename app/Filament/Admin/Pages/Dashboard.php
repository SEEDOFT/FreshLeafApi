<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\AdminCommissionWidget;
use App\Filament\Admin\Widgets\AdminRevenueChart;
use App\Filament\Admin\Widgets\AdminStatsOverview;
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
            CustomAccountWidget::class,
            AdminCommissionWidget::class,
            AdminStatsOverview::class,
            AdminRevenueChart::class,
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
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.dashboard');
    }
}
