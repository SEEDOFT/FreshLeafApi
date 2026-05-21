<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Pages;

use App\Filament\Vendor\Pages\ProductCatalog;
use App\Filament\Vendor\Resources\ProductInventories\ProductInventoryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;
use Override;

class ListProductInventories extends ListRecords
{
    #[Override]
    protected static string $resource = ProductInventoryResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(new HtmlString('<strong>'.__('shared.form.add_product').'</strong>'))
                ->url(static fn (): string => ProductCatalog::getUrl(panel: 'vendor')),
        ];
    }
}
