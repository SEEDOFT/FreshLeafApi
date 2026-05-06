<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters;

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
    protected static ?string $navigationLabel = 'Store Settings';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    #[Override]
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
