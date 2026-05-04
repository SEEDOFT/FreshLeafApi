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
                Section::make(__('admin.resources.user.account_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name')
                            ->label(__('admin.profile.first_name')),
                        TextEntry::make('last_name')
                            ->label(__('admin.profile.last_name')),
                        TextEntry::make('email')
                            ->label(__('admin.profile.email'))
                            ->placeholder('-'),
                        TextEntry::make('phone_number')
                            ->label(__('admin.profile.phone'))
                            ->placeholder('-'),
                        TextEntry::make('type.name')
                            ->label(__('admin.resources.user.type'))
                            ->badge()
                            ->placeholder('-')
                            ->color(static fn (string $state): string => match ($state) {
                                'Admin' => 'danger',
                                'Vendor' => 'warning',
                                'Consumer' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('status.name')
                            ->label(__('admin.resources.user.status'))
                            ->placeholder('-')
                            ->badge()
                            ->color(static fn (string $state): string => match ($state) {
                                'Active' => 'success',
                                'Pending' => 'warning',
                                'Inactive', 'Deleted' => 'danger',
                                default => 'gray',
                            }),
                    ]),

                Section::make(__('admin.resources.user.personal_info'))
                    ->schema([
                        ImageEntry::make('image')
                            ->label(__('admin.profile.avatar'))
                            ->disk('public')
                            ->circular()
                            ->imageSize(200)
                            ->defaultImageUrl(Storage::disk('public')->url('users/user.png')),
                    ]),

                Section::make(__('admin.resources.user.wallets_info'))
                    ->schema([
                        RepeatableEntry::make('wallets')
                            ->label(__('admin.resources.user.wallets_info'))
                            ->schema([
                                TextEntry::make('currency.name')
                                    ->label(__('admin.resources.wallet.currency'))
                                    ->placeholder('-'),
                                TextEntry::make('balance')
                                    ->label(__('admin.resources.wallet.balance'))
                                    ->placeholder('0.00')
                                    ->money(static fn (Wallet $record) => $record->currency->code),
                            ])
                            ->columns(2),
                    ]),

                Section::make(__('admin.resources.user.system_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime('d M Y, h:i A'),
                    ]),
            ]);
    }
}
