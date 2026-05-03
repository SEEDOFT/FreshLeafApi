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
                Section::make('Account Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                            ->unique(ignoreRecord: true),
                        Select::make('user_type_id')
                            ->label('Account Type')
                            ->relationship('type', 'name')
                            ->default(UserType::USER)
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                            ->live(),
                        Select::make('user_status_id')
                            ->label('Account Status')
                            ->relationship('status', 'name')
                            ->default(UserStatus::ACTIVE)
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                    ]),
            ]);
    }
}
