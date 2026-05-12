<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\Tables;

use App\Models\Currency;
use App\Models\Wallet;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.user.full_name'))
                    ->getStateUsing(static fn (Wallet $record) => $record->user ? $record->user->fullName : '-')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label(__('admin.resources.user.email'))
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('admin.resources.wallet.currency'))
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('balance')
                    ->label(__('admin.resources.wallet.balance'))
                    ->getStateUsing(static function (Wallet $record): string {
                        $id = $record->currency->id;
                        $symbol = $record->currency->symbol ?? '';
                        $balance = number_format((float) $record->balance, 2);

                        return $id === Currency::USD_ID
                            ? "{$symbol} {$balance}"
                            : "{$balance} {$symbol}";
                    })
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
