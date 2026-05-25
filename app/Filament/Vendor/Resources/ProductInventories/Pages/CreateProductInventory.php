<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Pages;

use App\Filament\Vendor\Resources\ProductInventories\ProductInventoryResource;
use App\Models\VendorInventoryStatus;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Override;

class CreateProductInventory extends CreateRecord
{
    #[Override]
    protected static string $resource = ProductInventoryResource::class;

    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['vendor_id'] = Auth::id();
        $data['inventory_status_id'] = VendorInventoryStatus::PENDING_REVIEW_ID;

        return $data;
    }
}
