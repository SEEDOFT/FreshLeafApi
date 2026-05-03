<?php

declare(strict_types=1);

namespace App\Filament\Resources\WalletTransactions\Schemas;

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
                Section::make('Transaction Details')
                    ->columns(2)
                    ->schema([
                        Select::make('wallet_id')
                            ->relationship('wallet.user', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->wallets->first()?->currency->code})")
                            ->searchable()
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('wallet_transaction_type_id')
                            ->relationship('type', 'name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('wallet_transaction_status_id')
                            ->relationship('status', 'name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        TextInput::make('amount')
                            ->numeric()
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                            ->prefix('$'),
                        TextInput::make('reference_type'),
                        TextInput::make('reference_id')
                            ->numeric(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
