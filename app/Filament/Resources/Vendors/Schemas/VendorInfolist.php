<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Infolists\Components\IconEntry;
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
                Section::make('Vendor Profile')
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('business_name'),
                        TextEntry::make('contact_phone')
                            ->placeholder('-'),
                        TextEntry::make('city')
                            ->placeholder('-'),
                        TextEntry::make('province')
                            ->placeholder('-'),
                        TextEntry::make('address')
                            ->columnSpanFull()
                            ->placeholder('-'),
                        IconEntry::make('is_verified')
                            ->boolean(),
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

                Section::make('Account Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name'),
                        TextEntry::make('last_name'),
                        TextEntry::make('email')
                            ->placeholder('-'),
                        TextEntry::make('phone_number'),
                        TextEntry::make('status.name')
                            ->badge()
                            ->color(static fn (string $state): string => match ($state) {
                                'Active' => 'success',
                                'Pending' => 'warning',
                                'Inactive', 'Deleted' => 'danger',
                                default => 'gray',
                            }),
                    ]),

                Section::make('System Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),
            ]);
    }
}
