<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\VendorInventoryRatings\Pages;

use App\Filament\Vendor\Resources\VendorInventoryRatings\VendorInventoryRatingResource;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewVendorInventoryRating extends ViewRecord
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
