<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Override;

class VendorProfile extends BaseEditProfile
{
    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->required(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])->columns(2),

                Section::make('Business Profile')
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

                Section::make('Financial Details')
                    ->relationship('vendorProfile')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->placeholder('e.g. ABA, ACLEDA')
                            ->maxLength(255),
                        TextInput::make('bank_account_name')
                            ->label('Account Holder Name')
                            ->maxLength(255),
                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(255),
                    ])->columns(3),

                Section::make('Identity & Verification')
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
                            ->label('Organic Certificate (Optional)')
                            ->directory('vendor-verification')
                            ->disabled(fn ($record) => $record?->is_verified),
                    ])->columns(2),
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
