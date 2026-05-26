<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ExchangeRates\Pages;

use App\Filament\Vendor\Resources\ExchangeRates\ExchangeRateResource;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListExchangeRates extends ListRecords
{
    #[Override]
    protected static string $resource = ExchangeRateResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
