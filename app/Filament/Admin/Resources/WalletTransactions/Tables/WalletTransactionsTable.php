<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WalletTransactions\Tables;

use App\Models\Order;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WalletTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->recordClasses(fn (WalletTransaction $record) => match ($record->wallet_transaction_status_id) {
                WalletTransactionStatus::PENDING_ID => 'bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400',
                WalletTransactionStatus::COMPLETED_ID => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                WalletTransactionStatus::FAILED_ID, WalletTransactionStatus::CANCELLED_ID => 'bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
                default => null,
            })
            ->columns([
                TextColumn::make('wallet.user.fullName')
                    ->label(__('admin.resources.wallet_transaction.user'))
                    ->getStateUsing(fn (WalletTransaction $record) => $record->wallet->user?->fullName)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('wallet.user.type.translated_name')
                    ->label(__('admin.resources.user.type') ?? 'User Type')
                    ->badge()
                    ->color(fn (WalletTransaction $record) => $record->wallet->user?->type?->getColor() ?? 'gray'),
                TextColumn::make('wallet.user.status.translated_name')
                    ->label(__('admin.resources.user.status') ?? 'Status')
                    ->badge()
                    ->color(fn (WalletTransaction $record) => $record->wallet->user?->status?->getColor() ?? 'gray'),
                TextColumn::make('type.translated_name')
                    ->label(__('admin.resources.wallet_transaction.type'))
                    ->badge()
                    ->color(fn (WalletTransaction $record): string => $record->type?->getColor() ?? 'gray')
                    ->sortable(),
                TextColumn::make('status.translated_name')
                    ->label(__('admin.resources.wallet_transaction.status'))
                    ->badge()
                    ->color(fn (WalletTransaction $record): string => $record->status?->getColor() ?? 'gray')
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('admin.resources.order.currency'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('amount')
                    ->label(__('admin.resources.wallet_transaction.amount'))
                    ->formatStateUsing(fn (WalletTransaction $record): string => Order::formatMoney($record->amount, $record->currency))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('wallet_transaction_type_id')
                    ->label(__('admin.resources.wallet_transaction.type'))
                    ->options(fn () => WalletTransactionType::all()->pluck('translated_name', 'id')),
                SelectFilter::make('wallet_transaction_status_id')
                    ->label(__('admin.resources.wallet_transaction.status'))
                    ->options(fn () => WalletTransactionStatus::all()->pluck('translated_name', 'id')),
            ])
            ->recordAction(ViewAction::class)
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
