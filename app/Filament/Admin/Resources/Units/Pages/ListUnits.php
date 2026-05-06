<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Units\Pages;

use App\Filament\Admin\Resources\Units\UnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListUnits extends ListRecords
{
    #[Override]
    protected static string $resource = UnitResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
