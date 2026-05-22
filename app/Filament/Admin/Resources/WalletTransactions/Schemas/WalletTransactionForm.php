<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WalletTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WalletTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.wallet_transaction.details'))
                    ->columns(2)
                    ->schema([
                        Select::make('wallet_id')
                            ->label(__('admin.resources.wallet_transaction.wallet'))
                            ->relationship('wallet.user', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->wallets->first()?->currency->code})")
                            ->searchable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('wallet_transaction_type_id')
                            ->label(__('admin.resources.wallet_transaction.type'))
                            ->relationship('type', 'name')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('wallet_transaction_status_id')
                            ->label(__('admin.resources.wallet_transaction.status'))
                            ->relationship('status', 'name')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        TextInput::make('amount')
                            ->label(__('admin.resources.wallet_transaction.amount'))
                            ->numeric()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->prefix('$'),
                        TextInput::make('reference_type')
                            ->label(__('admin.resources.wallet_transaction.ref_type')),
                        TextInput::make('reference_id')
                            ->label(__('admin.resources.wallet_transaction.ref_id'))
                            ->numeric(),
                        Textarea::make('description')
                            ->label(__('admin.resources.wallet_transaction.description'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
