<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\UserType;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name'),
                        TextEntry::make('last_name'),
                        TextEntry::make('email')
                            ->placeholder('-'),
                        TextEntry::make('phone_number'),
                        TextEntry::make('type.name')
                            ->badge()
                            ->color(static fn (string $state): string => match ($state) {
                                'Admin' => 'danger',
                                'Vendor' => 'warning',
                                'Consumer' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('status.name')
                            ->badge()
                            ->color(static fn (string $state): string => match ($state) {
                                'Active' => 'success',
                                'Pending' => 'warning',
                                'Inactive', 'Deleted' => 'danger',
                                default => 'gray',
                            }),
                    ]),

                Section::make('Vendor Profile')
                    ->relationship('vendorProfile')
                    ->hidden(static fn ($record) => $record->user_type_id !== UserType::VENDOR)
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

                Section::make('Admin Profile')
                    ->relationship('adminProfile')
                    ->hidden(static fn ($record) => $record->user_type_id !== UserType::ADMIN)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('job_title')
                            ->placeholder('-'),
                        TextEntry::make('department')
                            ->placeholder('-'),
                        IconEntry::make('super_admin')
                            ->boolean(),
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
