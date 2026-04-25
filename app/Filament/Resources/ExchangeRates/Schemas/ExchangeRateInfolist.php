<?php

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
                    ->label('Base Currency'),
                TextEntry::make('toCurrency.name')
                    ->label('Target Currency'),
                TextEntry::make('rate')
                    ->label('Conversion Rate')
                    ->numeric(),
                TextEntry::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime(),
            ]);
    }
}
