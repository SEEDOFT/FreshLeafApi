<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryBatches\Pages;

use App\Filament\Resources\InventoryBatches\InventoryBatchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditInventoryBatch extends EditRecord
{
    protected static string $resource = InventoryBatchResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
