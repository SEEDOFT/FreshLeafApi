<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WalletTransactions\Tables;

use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class WalletTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('wallet.user.name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.wallet_transaction.user'))
                    ->getStateUsing(static fn (WalletTransaction $record) => $record->wallet->user->fullName)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type.translated_name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.wallet_transaction.type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('status.translated_name')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.wallet_transaction.status'))
                    ->badge()
                    ->color(fn (WalletTransaction $record): string => match ($record->status->id) {
                        WalletTransactionStatus::COMPLETED_ID => 'success',
                        WalletTransactionStatus::PENDING_ID => 'warning',
                        WalletTransactionStatus::FAILED_ID, WalletTransactionStatus::CANCELLED_ID => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('amount')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.wallet_transaction.amount'))
                    ->money(fn (WalletTransaction $record) => $record->wallet->currency->code ?? 'USD')
                    ->sortable(),
                TextColumn::make('created_at')->placeholder(__('admin.resources.general.not_provided'))
                    ->label(__('admin.resources.created_at'))
                    ->dateTime()
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
