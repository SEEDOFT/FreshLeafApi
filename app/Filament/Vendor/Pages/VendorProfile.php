<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Override;

class VendorProfile extends BaseEditProfile
{
    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Store / Owner Avatar')
                                    ->avatar()
                                    ->imageEditor()
                                    ->directory('avatars')
                                    ->alignCenter()
                                    ->columnSpan(1),

                                Grid::make(2)
                                    ->schema([
                                        $this->getNameFormComponent(),
                                        $this->getEmailFormComponent(),
                                        TextInput::make('phone_number')
                                            ->label('Phone Number')
                                            ->tel()
                                            ->required(),
                                    ])
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->compact(),

                Tabs::make('Profile Tabs')
                    ->tabs([
                        Tab::make('Business Identity')
                            ->icon('heroicon-m-building-storefront')
                            ->schema([
                                Section::make('Store Information')
                                    ->description('Publicly visible details about your business.')
                                    ->relationship('vendorProfile')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('business_name')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('contact_phone')
                                                    ->tel()
                                                    ->maxLength(255),
                                                TextInput::make('city')
                                                    ->maxLength(255),
                                                TextInput::make('province')
                                                    ->maxLength(255),
                                            ]),
                                        TextInput::make('address')
                                            ->columnSpanFull()
                                            ->maxLength(255),
                                    ]),
                            ]),

                        Tab::make('Financials & Payouts')
                            ->icon('heroicon-m-banknotes')
                            ->schema([
                                Section::make('Bank Account Details')
                                    ->description('Used for weekly earnings payouts.')
                                    ->relationship('vendorProfile')
                                    ->schema([
                                        TextInput::make('bank_name')
                                            ->label('Bank Name')
                                            ->placeholder('e.g. ABA Bank')
                                            ->maxLength(255),
                                        TextInput::make('bank_account_name')
                                            ->label('Account Holder Name')
                                            ->placeholder('e.g. KOY YOTRABOTH')
                                            ->maxLength(255),
                                        TextInput::make('bank_account_number')
                                            ->label('Account Number')
                                            ->maxLength(255),
                                    ])->columns(3),
                            ]),

                        Tab::make('Verification Docs')
                            ->icon('heroicon-m-shield-check')
                            ->schema([
                                Section::make('Required Documents')
                                    ->description('Identity and physical store proof. Cannot be edited once verified.')
                                    ->relationship('vendorProfile')
                                    ->schema([
                                        FileUpload::make('id_card_front')
                                            ->label('ID Card (Front)')
                                            ->image()
                                            ->directory('vendor-verification')
                                            ->disabled(fn ($record) => $record?->is_verified),
                                        FileUpload::make('id_card_back')
                                            ->label('ID Card (Back)')
                                            ->image()
                                            ->directory('vendor-verification')
                                            ->disabled(fn ($record) => $record?->is_verified),
                                        FileUpload::make('store_front_image')
                                            ->label('Farm / Store Photo')
                                            ->image()
                                            ->directory('vendor-verification')
                                            ->disabled(fn ($record) => $record?->is_verified),
                                        FileUpload::make('organic_certificate_url')
                                            ->label('Organic Certificate')
                                            ->directory('vendor-verification')
                                            ->disabled(fn ($record) => $record?->is_verified),
                                    ])->columns(2),
                            ]),

                        Tab::make('Security')
                            ->icon('heroicon-m-lock-closed')
                            ->schema([
                                Section::make('Change Password')
                                    ->description('Update your account password.')
                                    ->schema([
                                        $this->getPasswordFormComponent(),
                                        $this->getPasswordConfirmationFormComponent(),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    protected function getNameFormComponent(): Component
    {
        return Grid::make(2)
            ->schema([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
