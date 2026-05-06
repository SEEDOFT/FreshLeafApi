<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\Pages;

use App\Filament\Admin\Resources\VendorInventories\VendorInventoryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewVendorInventory extends ViewRecord
{
    protected static string $resource = VendorInventoryResource::class;
}
