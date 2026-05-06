<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\Pages;

use App\Filament\Admin\Resources\VendorInventories\VendorInventoryResource;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewVendorInventory extends ViewRecord
{
    #[Override]
    protected static string $resource = VendorInventoryResource::class;
}
