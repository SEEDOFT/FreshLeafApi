<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExchangeRates\Pages;

use App\Filament\Admin\Resources\ExchangeRates\ExchangeRateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewExchangeRate extends ViewRecord
{
    protected static string $resource = ExchangeRateResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
