<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Payouts\Pages;

use App\Filament\Admin\Resources\Payouts\PayoutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListPayouts extends ListRecords
{
    protected static string $resource = PayoutResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
