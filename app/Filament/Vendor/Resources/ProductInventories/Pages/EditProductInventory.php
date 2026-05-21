<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Pages;

use App\Filament\Vendor\Resources\ProductInventories\ProductInventoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditProductInventory extends EditRecord
{
    #[Override]
    protected static string $resource = ProductInventoryResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
