<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExchangeRates\Tables;

use App\Models\Currency;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExchangeRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fromCurrency.translated_currency')
                    ->label(__('admin.resources.exchange_rate.base_currency'))
                    ->searchable(),
                TextColumn::make('toCurrency.translated_currency')
                    ->label(__('admin.resources.exchange_rate.target_currency'))
                    ->searchable(),
                TextColumn::make('rate')
                    ->label(__('admin.resources.exchange_rate.rate'))
                    ->numeric(8)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('from_currency_id')
                    ->label(__('admin.resources.exchange_rate.filter_currency_pair'))
                    ->options(
                        Currency::all()
                            ->pluck('translated_currency', 'id')
                    ),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
