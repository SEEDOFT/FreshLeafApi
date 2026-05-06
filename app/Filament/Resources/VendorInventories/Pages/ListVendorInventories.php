<?php

declare(strict_types=1);

namespace App\Filament\Resources\VendorInventories\Pages;

use App\Filament\Resources\VendorInventories\VendorInventoryResource;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListVendorInventories extends ListRecords
{
    protected static string $resource = VendorInventoryResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            // Monitoring only
        ];
    }
}
