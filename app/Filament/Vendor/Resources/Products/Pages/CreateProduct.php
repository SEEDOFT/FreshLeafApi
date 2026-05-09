<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Products\Pages;

use App\Filament\Vendor\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateProduct extends CreateRecord
{
    #[Override]
    protected static string $resource = ProductResource::class;

    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['vendor_id'] = auth()->id();

        return $data;
    }
}
