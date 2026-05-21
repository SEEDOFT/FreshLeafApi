<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Pages;

use App\Filament\Vendor\Resources\ProductInventories\ProductInventoryResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateProductInventory extends CreateRecord
{
    #[Override]
    protected static string $resource = ProductInventoryResource::class;

    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['vendor_id'] = auth()->id();

        return $data;
    }
}
