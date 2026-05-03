<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Wallet;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name')
                            ->label('First Name'),
                        TextEntry::make('last_name')
                            ->label('Last Name'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('-'),
                        TextEntry::make('phone_number')
                            ->label('Phone Number')
                            ->placeholder('-'),
                        TextEntry::make('type.name')
                            ->label('Account Type')
                            ->badge()
                            ->placeholder('-')
                            ->color(static fn (string $state): string => match ($state) {
                                'Admin' => 'danger',
                                'Vendor' => 'warning',
                                'Consumer' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('status.name')
                            ->label('Account Status')
                            ->placeholder('-')
                            ->badge()
                            ->color(static fn (string $state): string => match ($state) {
                                'Active' => 'success',
                                'Pending' => 'warning',
                                'Inactive', 'Deleted' => 'danger',
                                default => 'gray',
                            }),
                    ]),

                Section::make('Personal Information')
                    ->schema([
                        ImageEntry::make('image')
                            ->label('Profile')
                            ->disk('public')
                            ->circular()
                            ->imageSize(200)
                            ->defaultImageUrl(Storage::disk('public')->url('users/user.png')),

                        TextEntry::make('userProfile.gender')
                            ->label('Gender')
                            ->placeholder('-'),
                    ]),

                Section::make('Wallets Information')
                    ->schema([
                        RepeatableEntry::make('wallets')
                            ->schema([
                                TextEntry::make('currency.name')
                                    ->label('Currency')
                                    ->placeholder('-'),
                                TextEntry::make('balance')
                                    ->label('Balance')
                                    ->placeholder('0.00')
                                    ->money(static fn (Wallet $record) => $record->currency->code),
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
