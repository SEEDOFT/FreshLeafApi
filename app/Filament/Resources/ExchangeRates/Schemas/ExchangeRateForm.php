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
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->columnSpanFull(),
                Select::make('to_currency_id')
                    ->label('Target Currency')
                    ->relationship('toCurrency', 'name')
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->columnSpanFull(),
                TextInput::make('rate')
                    ->label('Conversion Rate')
                    ->helperText('e.g. 1 USD = 4100 KHR')
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->numeric()
                    ->columnSpanFull(),
            ]);
    }
}
