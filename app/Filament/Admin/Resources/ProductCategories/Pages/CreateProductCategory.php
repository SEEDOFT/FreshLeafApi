<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ProductCategories\Pages;

use App\Filament\Admin\Resources\ProductCategories\ProductCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateProductCategory extends CreateRecord
{
    #[Override]
    protected static string $resource = ProductCategoryResource::class;

    #[Override]
    protected static bool $canCreateAnother = false;
}
