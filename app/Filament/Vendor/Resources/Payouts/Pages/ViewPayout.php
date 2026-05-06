<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Payouts\Pages;

use App\Filament\Vendor\Resources\Payouts\PayoutResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewPayout extends ViewRecord
{
    #[Override]
    protected static string $resource = PayoutResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
