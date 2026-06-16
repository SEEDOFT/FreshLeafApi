<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Payouts\Pages;

use App\Filament\Admin\Resources\Payouts\PayoutResource;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditPayout extends EditRecord
{
    #[Override]
    protected static string $resource = PayoutResource::class;


    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
