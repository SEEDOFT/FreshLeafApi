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
            ->recordClasses(fn () => 'bg-gray-50 dark:bg-gray-900/50 border-l-4 border-gray-400')
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
                    ->dateTime('h:i A, d M Y')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('from_currency_id')
                    ->label(__('admin.resources.exchange_rate.filter_currency_pair'))
                    ->options(fn () => Currency::all()->pluck('translated_currency', 'id')),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
