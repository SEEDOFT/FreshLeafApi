<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExchangeRates\Schemas;

use App\Models\Currency;
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
                    ->options(
                        Currency::all()
                            ->pluck('translated_currency', 'id')
                    )
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->columnSpanFull(),
                Select::make('to_currency_id')
                    ->label(new HtmlString('<strong>'.__('admin.resources.exchange_rate.target_currency').'</strong>'))
                    ->options(
                        Currency::all()
                            ->pluck('translated_currency', 'id')
                    )
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->columnSpanFull(),
                TextInput::make('rate')
                    ->label(new HtmlString('<strong>'.__('admin.resources.exchange_rate.rate').'</strong>'))
                    ->helperText(__('admin.resources.exchange_rate.rate_helper'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->numeric()
                    ->columnSpanFull(),
            ]);
    }
}
