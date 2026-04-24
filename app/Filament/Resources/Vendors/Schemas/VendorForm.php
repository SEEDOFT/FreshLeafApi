<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorForm
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
                            ->email(),
                        TextInput::make('phone_number')
                            ->tel()
                            ->required(),
                        Select::make('user_status_id')
                            ->relationship('status', 'name')
                            ->required(),
                    ]),

                Section::make('Business Profile')
                    ->relationship('vendorProfile')
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
            ]);
    }
}
