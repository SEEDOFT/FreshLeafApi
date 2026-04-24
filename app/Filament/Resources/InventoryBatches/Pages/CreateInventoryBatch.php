<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryBatches\Pages;

use App\Filament\Resources\InventoryBatches\InventoryBatchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryBatch extends CreateRecord
{
    protected static string $resource = InventoryBatchResource::class;
}
