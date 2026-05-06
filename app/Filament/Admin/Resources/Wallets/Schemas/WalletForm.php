<?php

declare(strict_types=1);

namespace App\Filament\Resources\Wallets\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class WalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.wallet.details'))
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet.user').'</strong>'))
                            ->relationship('user', 'first_name')
                            ->getOptionLabelFromRecordUsing(static fn (User $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(static fn (mixed $state): bool => filled($state)),
                        Select::make('currency_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet.currency').'</strong>'))
                            ->relationship('currency', 'name')
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(static fn (mixed $state): bool => filled($state)),
                        TextInput::make('balance')
                            ->label(new HtmlString('<strong>'.__('admin.resources.wallet.balance').'</strong>'))
                            ->numeric()
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(static fn (mixed $state): bool => filled($state))
                            ->default(0),
                    ]),
            ]);
    }
}
