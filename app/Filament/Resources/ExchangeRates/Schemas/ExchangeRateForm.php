<?php

namespace App\Filament\Resources\ExchangeRates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExchangeRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('from_currency_id')
                    ->label('Base Currency')
                    ->relationship('fromCurrency', 'name')
                    ->required()
                    ->columnSpanFull(),
                Select::make('to_currency_id')
                    ->label('Target Currency')
                    ->relationship('toCurrency', 'name')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('rate')
                    ->label('Conversion Rate')
                    ->helperText('e.g. 1 USD = 4100 KHR')
                    ->required()
                    ->numeric()
                    ->columnSpanFull(),
            ]);
    }
}
