<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExchangeRates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ExchangeRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('from_currency_id')
                    ->label(new HtmlString('<strong>'.__('admin.resources.exchange_rate.base_currency').'</strong>'))
                    ->relationship('fromCurrency', 'name')
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state))
                    ->columnSpanFull(),
                Select::make('to_currency_id')
                    ->label(new HtmlString('<strong>'.__('admin.resources.exchange_rate.target_currency').'</strong>'))
                    ->relationship('toCurrency', 'name')
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state))
                    ->columnSpanFull(),
                TextInput::make('rate')
                    ->label(new HtmlString('<strong>'.__('admin.resources.exchange_rate.rate').'</strong>'))
                    ->helperText(__('admin.resources.exchange_rate.rate_helper'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state))
                    ->numeric()
                    ->columnSpanFull(),
            ]);
    }
}
