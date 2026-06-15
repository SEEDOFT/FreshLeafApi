<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Wallets\RelationManagers;

use App\Models\Order;
use App\Models\Payout;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->recordClasses(fn (WalletTransaction $record) => match ($record->wallet_transaction_status_id) {
                WalletTransactionStatus::PENDING_ID => 'bg-yellow-50 dark:bg-yellow-900/50 border-l-4 border-yellow-400',
                WalletTransactionStatus::COMPLETED_ID => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                WalletTransactionStatus::FAILED_ID, WalletTransactionStatus::CANCELLED_ID => 'bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
                default => null,
            })
            ->columns([
                TextColumn::make('amount')
                    ->label(__('admin.resources.wallet_transaction.amount'))
                    ->formatStateUsing(fn ($state, $record) => Order::formatMoney((float) $state, $record->currency))
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                TextColumn::make('type.translated_name')
                    ->label(__('admin.resources.wallet_transaction.type'))
                    ->badge()
                    ->color(fn ($record) => match ($record->wallet_transaction_type_id) {
                        WalletTransactionType::DEPOSIT_ID => 'success',
                        WalletTransactionType::WITHDRAWAL_ID => 'warning',
                        WalletTransactionType::PAYMENT_ID => 'danger',
                        WalletTransactionType::REFUND_ID => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('status.translated_name')
                    ->label(__('admin.resources.wallet_transaction.status'))
                    ->badge()
                    ->color(fn ($record) => match ($record->wallet_transaction_status_id) {
                        WalletTransactionStatus::COMPLETED_ID => 'success',
                        WalletTransactionStatus::PENDING_ID => 'warning',
                        WalletTransactionStatus::FAILED_ID,
                        WalletTransactionStatus::CANCELLED_ID => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('transaction_date')
                    ->label(__('admin.resources.wallet_transaction.transaction_date'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('admin.resources.wallet_transaction.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('reference.order_number')
                    ->label(__('admin.resources.order.order_number'))
                    ->visible(fn ($record) => $record && $record->reference_type === Order::class),

                TextColumn::make('reference.payout_number')
                    ->label(__('admin.resources.payout.payout_number'))
                    ->visible(fn ($record) => $record && $record->reference_type === Payout::class),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
