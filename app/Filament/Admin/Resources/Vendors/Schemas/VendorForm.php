<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Schemas;

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
                Section::make(__('admin.resources.vendor.account_info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label(__('admin.resources.user.first_name'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        TextInput::make('last_name')
                            ->label(__('admin.resources.user.last_name'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        TextInput::make('email')
                            ->label(__('admin.resources.user.email'))
                            ->email(),
                        TextInput::make('phone_number')
                            ->label(__('admin.resources.user.phone'))
                            ->tel()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('user_status_id')
                            ->label(__('admin.resources.user.account_status'))
                            ->relationship('status', 'name')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                    ]),

                Section::make(__('admin.resources.vendor.business_profile'))
                    ->relationship('vendorProfile')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('business_name')
                                    ->label(__('admin.resources.vendor.business_name'))
                                    ->required(fn (string $operation): bool => $operation === 'create'),
                                TextInput::make('contact_phone')
                                    ->label(__('admin.resources.vendor.contact_phone')),
                                TextInput::make('city')
                                    ->label(__('admin.resources.vendor.city')),
                                TextInput::make('province')
                                    ->label(__('admin.resources.vendor.province')),
                            ]),
                        TextInput::make('address')
                            ->label(__('admin.resources.vendor.address'))
                            ->columnSpanFull(),
                        Toggle::make('is_verified')
                            ->label(__('admin.resources.vendor.verified'))
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Section::make(__('admin.resources.vendor.identity_verification'))
                    ->relationship('vendorProfile')
                    ->schema([
                        FileUpload::make('id_card_front')
                            ->label(__('admin.resources.vendor.id_card_front'))
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        FileUpload::make('id_card_back')
                            ->label(__('admin.resources.vendor.id_card_back'))
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        FileUpload::make('store_front_image')
                            ->label(__('admin.resources.vendor.store_photo'))
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        FileUpload::make('organic_certificate_url')
                            ->label(__('admin.resources.vendor.organic_cert'))
                            ->disk('local')
                            ->directory('vendor-verification'),
                    ])->columns(2),

                Section::make(__('admin.resources.vendor.financial_details'))
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        Select::make('bank_name')
                            ->label(__('admin.resources.vendor.bank_name'))
                            ->options([
                                // 'Wallet' => 'Wallet',
                                'ABA' => 'ABA',
                                'Acleda' => 'Acleda',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->columnSpanFull(),

                        TextInput::make('bank_account_name')
                            ->label(__('admin.resources.vendor.account_holder'))
                            ->placeholder('KOY YOTRABOTH')
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        TextInput::make('bank_account_number')
                            ->label(__('admin.resources.vendor.account_number'))
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        FileUpload::make('bank_qr_code')
                            ->label(__('admin.resources.vendor.qr_code'))
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
