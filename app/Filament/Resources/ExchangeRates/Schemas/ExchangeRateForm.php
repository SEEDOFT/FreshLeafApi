<?php

declare(strict_types=1);

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
                    ->label(__('admin.resources.exchange_rate.base_currency'))
                    ->relationship('fromCurrency', 'name')
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->columnSpanFull(),
                Select::make('to_currency_id')
                    ->label(__('admin.resources.exchange_rate.target_currency'))
                    ->relationship('toCurrency', 'name')
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->columnSpanFull(),
                TextInput::make('rate')
                    ->label(__('admin.resources.exchange_rate.rate'))
                    ->helperText(__('admin.resources.exchange_rate.rate_helper'))
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->numeric()
                    ->columnSpanFull(),
            ]);
    }
}
