<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ExchangeRates\Pages;

use App\Filament\Vendor\Resources\ExchangeRates\ExchangeRateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewExchangeRate extends ViewRecord
{
    #[Override]
    protected static string $resource = ExchangeRateResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
