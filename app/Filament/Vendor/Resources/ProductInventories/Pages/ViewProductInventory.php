<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Pages;

use App\Filament\Vendor\Resources\ProductInventories\ProductInventoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewProductInventory extends ViewRecord
{
    #[Override]
    protected static string $resource = ProductInventoryResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
