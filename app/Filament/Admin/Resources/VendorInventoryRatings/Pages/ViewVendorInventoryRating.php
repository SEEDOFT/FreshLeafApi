<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventoryRatings\Pages;

use App\Filament\Admin\Resources\VendorInventoryRatings\VendorInventoryRatingResource;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewVendorInventoryRating extends ViewRecord
{
    #[Override]
    protected static string $resource = VendorInventoryRatingResource::class;
}
