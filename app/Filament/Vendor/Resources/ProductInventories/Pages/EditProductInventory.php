<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Pages;

use App\Filament\Vendor\Resources\ProductInventories\ProductInventoryResource;
use App\Models\VendorInventoryStatus;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditProductInventory extends EditRecord
{
    #[Override]
    protected static string $resource = ProductInventoryResource::class;

    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['inventory_status_id'] = VendorInventoryStatus::PENDING_REVIEW_ID;

        return $data;
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
