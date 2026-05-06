<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\UserStatus;
use App\Models\UserType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.user.account_info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label(__('admin.resources.user.first_name'))
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(static fn ($state): bool => filled($state))
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('admin.resources.user.last_name'))
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(static fn ($state): bool => filled($state))
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('admin.resources.user.email'))
                            ->email()
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone_number')
                            ->label(__('admin.resources.user.phone'))
                            ->tel()
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(static fn ($state): bool => filled($state))
                            ->unique(ignoreRecord: true),
                        Select::make('user_type_id')
                            ->label(__('admin.resources.user.account_type'))
                            ->relationship('type', 'name')
                            ->default(UserType::USER)
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(static fn ($state): bool => filled($state))
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('user_status_id')
                            ->label(__('admin.resources.user.account_status'))
                            ->relationship('status', 'name')
                            ->default(UserStatus::ACTIVE)
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(static fn ($state): bool => filled($state))
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}
