<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExchangeRates\Pages;

use App\Filament\Admin\Resources\ExchangeRates\ExchangeRateResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateExchangeRate extends CreateRecord
{
    #[Override]
    protected static string $resource = ExchangeRateResource::class;

    #[Override]
    protected static bool $canCreateAnother = false;
}
