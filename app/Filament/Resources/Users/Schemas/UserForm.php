<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\UserType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                            ->required(),
                        TextInput::make('last_name')
                            ->required(),
                        TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone_number')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('user_type_id')
                            ->relationship('type', 'name')
                            ->required()
                            ->live(),
                        Select::make('user_status_id')
                            ->relationship('status', 'name')
                            ->required(),
                    ]),

                Section::make('Vendor Profile')
                    ->relationship('vendorProfile')
                    ->hidden(fn (callable $get) => (int) $get('user_type_id') !== UserType::VENDOR)
                    ->schema([
                        TextInput::make('business_name')
                            ->required(),
                        TextInput::make('contact_phone'),
                        TextInput::make('city'),
                        TextInput::make('province'),
                        TextInput::make('address')
                            ->columnSpanFull(),
                        Toggle::make('is_verified')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('Admin Profile')
                    ->relationship('adminProfile')
                    ->hidden(fn (callable $get) => (int) $get('user_type_id') !== UserType::ADMIN)
                    ->schema([
                        TextInput::make('job_title'),
                        TextInput::make('department'),
                        Toggle::make('super_admin'),
                    ]),
            ]);
    }
}
