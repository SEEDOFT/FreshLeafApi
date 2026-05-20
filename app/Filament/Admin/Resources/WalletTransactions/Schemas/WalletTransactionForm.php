<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WalletTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet_transaction.wallet').'</strong>'))
                            ->relationship('wallet.user', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->wallets->first()?->currency->code})")
                            ->searchable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('wallet_transaction_type_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet_transaction.type').'</strong>'))
                            ->relationship('type', 'name')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('wallet_transaction_status_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet_transaction.status').'</strong>'))
                            ->relationship('status', 'name')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        TextInput::make('amount')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet_transaction.amount').'</strong>'))
                            ->numeric()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->prefix('$'),
                        TextInput::make('reference_type')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet_transaction.ref_type').'</strong>')),
                        TextInput::make('reference_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet_transaction.ref_id').'</strong>'))
                            ->numeric(),
                        Textarea::make('description')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet_transaction.description').'</strong>'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
