<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Wallets\RelationManagers;

use App\Models\WalletTransaction;
use App\Models\WalletTransactionStatus;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class TransactionsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'transactions';

    #[Override]
    public function form(Schema $schema): Schema
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $schema
            ->columns(2)
            ->components([
                TextInput::make('type.translated_name')
                    ->label(__('admin.resources.wallet_transaction.type'))
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder($notProvided),
                TextInput::make('amount')
                    ->label(__('admin.resources.wallet_transaction.amount'))
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder($notProvided)
                    ->formatStateUsing(function (?string $state, WalletTransaction $record): string {
                        $symbol = $record->wallet->currency->symbol ?? '';
                        $amount = number_format((float) ($record->amount ?? 0), 2);

                        return "{$symbol} {$amount}";
                    }),
                TextInput::make('status.translated_name')
                    ->label(__('admin.resources.wallet_transaction.status'))
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder($notProvided),
                TextInput::make('description')
                    ->label(__('admin.resources.wallet_transaction.description'))
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder($notProvided),
                TextInput::make('transaction_date')
                    ->label(__('admin.resources.wallet_transaction.transaction_date'))
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder($notProvided),
                TextInput::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder($notProvided),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $table
            ->stackedOnMobile()
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('type.translated_name')
                    ->label(__('admin.resources.wallet_transaction.type'))
                    ->badge()
                    ->placeholder($notProvided)
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('admin.resources.wallet_transaction.amount'))
                    ->placeholder($notProvided)
                    ->getStateUsing(function (WalletTransaction $record): string {
                        $symbol = $record->wallet->currency->symbol ?? '';
                        $amount = number_format((float) $record->amount, 2);

                        return "{$symbol} {$amount}";
                    })
                    ->sortable(),
                TextColumn::make('status.translated_name')
                    ->label(__('admin.resources.wallet_transaction.status'))
                    ->badge()
                    ->placeholder($notProvided)
                    ->color(fn (WalletTransaction $record): string => match ($record->status->id) {
                        WalletTransactionStatus::COMPLETED_ID => 'success',
                        WalletTransactionStatus::PENDING_ID => 'warning',
                        WalletTransactionStatus::FAILED_ID,
                        WalletTransactionStatus::CANCELLED_ID => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('admin.resources.wallet_transaction.description'))
                    ->placeholder($notProvided)
                    ->limit(30),
                TextColumn::make('transaction_date')
                    ->label(__('admin.resources.wallet_transaction.transaction_date'))
                    ->placeholder($notProvided)
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
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
