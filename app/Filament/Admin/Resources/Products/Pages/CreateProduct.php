<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateProduct extends CreateRecord
{
    #[Override]
    protected static string $resource = ProductResource::class;
}
