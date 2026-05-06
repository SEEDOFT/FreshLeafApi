<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExchangeRates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExchangeRateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('fromCurrency.name')
                    ->label(__('admin.resources.exchange_rate.base_currency')),
                TextEntry::make('toCurrency.name')
                    ->label(__('admin.resources.exchange_rate.target_currency')),
                TextEntry::make('rate')
                    ->label(__('admin.resources.exchange_rate.rate'))
                    ->numeric(),
                TextEntry::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime(),
            ]);
    }
}
