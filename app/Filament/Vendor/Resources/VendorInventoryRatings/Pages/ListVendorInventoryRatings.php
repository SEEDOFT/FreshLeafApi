<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\VendorInventoryRatings\Pages;

use App\Filament\Vendor\Resources\VendorInventoryRatings\VendorInventoryRatingResource;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListVendorInventoryRatings extends ListRecords
{
    #[Override]
    protected static string $resource = VendorInventoryRatingResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
