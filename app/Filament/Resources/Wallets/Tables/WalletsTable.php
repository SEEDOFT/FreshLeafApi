<?php

declare(strict_types=1);

namespace App\Filament\Resources\Wallets\Tables;

use App\Models\Wallet;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.resources.wallet.user'))
                    // ->getStateUsing(static fn (Wallet $record) => "{$record->user->first_name} {$record->user->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('admin.resources.wallet.currency'))
                    ->sortable(),
                TextColumn::make('balance')
                    ->label(__('admin.resources.wallet.balance'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('admin.resources.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('currency_id')
                    ->label(__('admin.resources.wallet.currency'))
                    ->relationship('currency', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
