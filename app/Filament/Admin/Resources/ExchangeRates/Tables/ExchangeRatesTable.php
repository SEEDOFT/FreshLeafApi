<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExchangeRates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                TextColumn::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
