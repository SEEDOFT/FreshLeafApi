<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Pages;

use App\Filament\Vendor\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewOrder extends ViewRecord
{
    #[Override]
    protected static string $resource = OrderResource::class;
}
