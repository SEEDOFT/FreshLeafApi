<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
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
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        TextInput::make('last_name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        TextInput::make('email')
                            ->email(),
                        TextInput::make('phone_number')
                            ->tel()
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                        Select::make('user_status_id')
                            ->relationship('status', 'name')
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                    ]),

                Section::make('Business Profile')
                    ->relationship('vendorProfile')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('business_name')
                                    ->required(static fn (string $operation): bool => $operation === 'create'),
                                TextInput::make('contact_phone'),
                                TextInput::make('city'),
                                TextInput::make('province'),
                            ]),
                        TextInput::make('address')
                            ->columnSpanFull(),
                        Toggle::make('is_verified')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make('Identity Verification')
                    ->relationship('vendorProfile')
                    ->schema([
                        FileUpload::make('id_card_front')
                            ->label('ID Card (Front)')
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->required(static fn (string $operation): bool => $operation === 'create'),
                        FileUpload::make('id_card_back')
                            ->label('ID Card (Back)')
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->required(static fn (string $operation): bool => $operation === 'create'),
                        FileUpload::make('store_front_image')
                            ->label('Farm / Store Photo')
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->required(static fn (string $operation): bool => $operation === 'create'),
                        FileUpload::make('organic_certificate_url')
                            ->label('Organic Certificate (Optional)')
                            ->disk('local')
                            ->directory('vendor-verification'),
                    ])->columns(2),

                Section::make('Financial Details')
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        Select::make('bank_name')
                            ->label('Bank Name')
                            ->options([
                                // 'Wallet' => 'Wallet',
                                'ABA' => 'ABA',
                                'Acleda' => 'Acleda',
                            ])
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->columnSpanFull(),

                        TextInput::make('bank_account_name')
                            ->label('Account Holder Name')
                            ->placeholder('KOY YOTRABOTH')
                            ->required(static fn (string $operation): bool => $operation === 'create'),
                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->required(static fn (string $operation): bool => $operation === 'create'),

                        FileUpload::make('bank_qr_code')
                            ->label('Bank QR Code')
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
