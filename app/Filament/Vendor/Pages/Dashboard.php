<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Override;

class Dashboard extends BaseDashboard
{
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
