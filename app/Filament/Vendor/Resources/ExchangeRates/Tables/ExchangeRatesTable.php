<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ExchangeRates\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExchangeRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->columns([
                TextColumn::make('fromCurrency.translated_currency')
                    ->label(__('admin.resources.exchange_rate.base_currency'))
                    ->searchable(),
                TextColumn::make('toCurrency.translated_currency')
                    ->label(__('admin.resources.exchange_rate.target_currency'))
                    ->searchable(),
                TextColumn::make('rate')
                    ->label(__('admin.resources.exchange_rate.rate'))
                    ->numeric()
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
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
