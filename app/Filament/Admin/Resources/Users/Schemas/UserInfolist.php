<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->components([
                Section::make(__('admin.resources.user.account_info'))
                    ->columns(2)
                    ->schema([
                        Grid::make(1)
                            ->columnSpanFull()
                            ->schema([
                                ImageEntry::make('image')
                                    ->label(__('admin.profile.avatar'))
                                    ->getStateUsing(fn ($record) => resolve_image_url($record->image))
                                    ->circular()

                                    ->alignCenter()
                                    ->extraImgAttributes(fn () => [
                                        'class' => 'cursor-zoom-in',
                                        'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                                    ]),
                            ]),
                        TextEntry::make('first_name')
                            ->label(__('admin.resources.user.first_name')),
                        TextEntry::make('last_name')
                            ->label(__('admin.resources.user.last_name')),
                        TextEntry::make('email')
                            ->label(__('admin.resources.user.email')),
                        TextEntry::make('phone_number')
                            ->label(__('admin.resources.user.phone')),
                        TextEntry::make('type.translated_name')
                            ->label(__('admin.resources.user.type'))
                            ->badge()
                            ->color(fn (User $record): string => $record->type?->getColor() ?? 'gray'),
                        TextEntry::make('status.translated_name')
                            ->label(__('admin.resources.user.status'))
                            ->badge()
                            ->color(fn (User $record): string => $record->status?->getColor() ?? 'gray'),
                    ]),
                Section::make(__('admin.resources.vendor.business_profile'))
                    ->relationship('vendorProfile')
                    ->visible(fn (User $record) => $record->user_type_id === UserType::VENDOR_ID)
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('store_front_image')
                            ->label(__('admin.resources.vendor.store_photo'))
                            ->getStateUsing(fn ($record) => resolve_image_url($record->store_front_image))
                            ->circular()
                            ->disk(null)
                            ->columnSpanFull(),
                        TextEntry::make('business_name')
                            ->label(__('admin.resources.vendor.business_name')),
                        TextEntry::make('contact_phone')
                            ->label(__('admin.resources.vendor.contact_phone')),
                        TextEntry::make('address')
                            ->label(__('admin.resources.vendor.address'))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('admin.resources.user.wallets_info'))
                    ->schema([
                        RepeatableEntry::make('wallets')
                            ->label(__('admin.resources.user.wallets_info'))
                            ->columns(2)
                            ->schema([
                                TextEntry::make('currency.translated_currency')

                                    ->label(__('admin.resources.wallet.currency')),
                                TextEntry::make('balance')
                                    ->label(__('admin.resources.wallet.balance'))
                                    ->placeholder('0.00')
                                    ->getStateUsing(fn (Wallet $record): string => format_currency(
                                        $record->balance,
                                        $record->currency->code
                                    )),
                            ]),
                    ]),
                Section::make(__('admin.resources.user.system_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->label(__('admin.resources.created_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('updated_at')->label(__('admin.resources.updated_at'))
                            ->dateTime('h:i A, d M Y'),
                    ]),
            ]);
    }
}
