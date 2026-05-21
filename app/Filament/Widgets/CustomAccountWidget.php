<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget as BaseAccountWidget;
use Override;

class CustomAccountWidget extends BaseAccountWidget
{
    #[Override]
    protected int|string|array $columnSpan = 1;
}
