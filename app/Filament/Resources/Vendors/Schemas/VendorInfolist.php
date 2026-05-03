<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Account Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name')
                            ->label('First Name')
                            ->placeholder('-'),
                        TextEntry::make('last_name')
                            ->label('Last Name')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('-'),
                        TextEntry::make('phone_number')
                            ->label('Phone Number')
                            ->placeholder('-'),
                        TextEntry::make('type.name')
                            ->label('Account Type')
                            ->badge()
                            ->color(static fn (string $state): string => match ($state) {
                                'Admin' => 'danger',
                                'Vendor' => 'warning',
                                'Consumer' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('status.name')
                            ->label('Account Status')
                            ->badge()
                            ->color(static fn (string $state): string => match ($state) {
                                'Active' => 'success',
                                'Pending' => 'warning',
                                'Inactive', 'Deleted' => 'danger',
                                default => 'gray',
                            }),
                    ]),

                Section::make('Profile Information')
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('business_name')
                            ->label('Business Name')
                            ->placeholder('-'),
                        TextEntry::make('contact_phone')
                            ->label('Contact Phone')
                            ->placeholder('-'),
                        TextEntry::make('city')
                            ->label('City')
                            ->placeholder('-'),
                        TextEntry::make('province')
                            ->label('Province')
                            ->placeholder('-'),
                        TextEntry::make('address')
                            ->label('Address')
                            ->columnSpanFull()
                            ->color('info')
                            ->placeholder('-')
                            ->url(static fn (string $state): string => 'https://maps.google.com/?q='.urlencode($state))
                            ->openUrlInNewTab(),
                        IconEntry::make('is_verified')
                            ->label('Verification Status')
                            ->boolean(),

                    ]),

                Section::make('Verification Documents')
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('id_card_front')
                            ->label('ID Card (Front)')
                            ->imageUrl(static fn ($state) => $state ? route('admin.documents.show', ['path' => $state]) : null)
                            ->height(200),
                        ImageEntry::make('id_card_back')
                            ->label('ID Card (Back)')
                            ->imageUrl(static fn ($state) => $state ? route('admin.documents.show', ['path' => $state]) : null)
                            ->height(200),
                        ImageEntry::make('store_front_image')
                            ->label('Farm / Store Photo')
                            ->imageUrl(static fn ($state) => $state ? route('admin.documents.show', ['path' => $state]) : null)
                            ->height(200),
                        TextEntry::make('organic_certificate_url')
                            ->label('Organic Certificate')
                            ->placeholder('Not Provided')
                            ->url(static fn ($state) => $state ? route('admin.documents.show', ['path' => $state]) : null)
                            ->openUrlInNewTab()
                            ->color('primary'),
                    ]),

                Section::make('Financial Details')
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('bank_name')
                            ->label('Bank Name'),
                        TextEntry::make('bank_account_name')
                            ->label('Account Holder'),
                        TextEntry::make('bank_account_number')
                            ->label('Account Number'),
                        ImageEntry::make('bank_qr_code')
                            ->label('Bank QR Code')
                            ->url(static fn ($state) => $state ? route('admin.documents.show', ['path' => $state]) : null)
                            ->imageSize(200),
                    ]),

                Section::make('Wallets Information')
                    ->schema([
                        RepeatableEntry::make('wallets')
                            ->schema([
                                TextEntry::make('currency.name')
                                    ->label('Currency'),
                                TextEntry::make('balance')
                                    ->money(static fn ($record) => $record->currency->code),
                            ])
                            ->columns(2),
                    ]),

                Section::make('System Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, h:i A'),
                    ]),
            ]);
    }
}
