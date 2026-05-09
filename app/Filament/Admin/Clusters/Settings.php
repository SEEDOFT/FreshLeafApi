<?php

declare(strict_types=1);

namespace App\Filament\Admin\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use Override;

class Settings extends Cluster
{
    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.settings');
    }

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    #[Override]
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
