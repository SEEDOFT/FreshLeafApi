<?php

declare(strict_types=1);

namespace App\Filament\Resources\WalletTransactions\Tables;

use App\Models\WalletTransaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WalletTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('wallet.user.name')
                    ->label(__('admin.resources.wallet_transaction.user'))
                    ->getStateUsing(fn (WalletTransaction $record) => "{$record->wallet->user?->first_name} {$record->wallet->user?->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('type.name')
                    ->label(__('admin.resources.wallet_transaction.type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('status.name')
                    ->label(__('admin.resources.wallet_transaction.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Completed' => 'success',
                        'Pending' => 'warning',
                        'Failed', 'Cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('admin.resources.wallet_transaction.amount'))
                    ->money(fn (WalletTransaction $record) => $record->wallet->currency->code ?? 'USD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('wallet_transaction_type_id')
                    ->label(__('admin.resources.wallet_transaction.type'))
                    ->relationship('type', 'name'),
                SelectFilter::make('wallet_transaction_status_id')
                    ->label(__('admin.resources.wallet_transaction.status'))
                    ->relationship('status', 'name'),
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
