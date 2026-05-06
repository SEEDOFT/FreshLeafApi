<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Units\Pages;

use App\Filament\Admin\Resources\Units\UnitResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateUnit extends CreateRecord
{
    #[Override]
    protected static string $resource = UnitResource::class;
}
