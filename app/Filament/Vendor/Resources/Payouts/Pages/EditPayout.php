<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Pages;

use App\Filament\Vendor\Resources\Payouts\PayoutResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
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
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
