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
            ->columns([
                TextColumn::make('wallet.user.fullName')
                    ->label(__('admin.resources.wallet_transaction.user'))
                    // ->getStateUsing(fn (WalletTransaction $record) => $record->wallet->user->fullName)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type.translated_name')
                    ->label(__('admin.resources.wallet_transaction.type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('status.translated_name')
                    ->label(__('admin.resources.wallet_transaction.status'))
                    ->badge()
                    ->color(fn (WalletTransaction $record): string => match ($record->status->id) {
                        WalletTransactionStatus::COMPLETED_ID => 'success',
                        WalletTransactionStatus::PENDING_ID => 'warning',
                        WalletTransactionStatus::FAILED_ID, WalletTransactionStatus::CANCELLED_ID => 'danger',
                        default => 'gray',
                    })
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
                    ->options(
                        WalletTransactionType::all()
                            ->pluck('translated_name', 'id')
                    ),
                SelectFilter::make('wallet_transaction_status_id')
                    ->label(__('admin.resources.wallet_transaction.status'))
                    ->options(
                        WalletTransactionStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
            ])
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
