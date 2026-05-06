<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Schemas;

use App\Models\Currency;
use App\Models\Wallet;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class VendorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $schema
            ->components([

                Section::make(__('admin.resources.vendor.account_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('first_name')
                            ->label(__('admin.profile.first_name'))
                            ->placeholder($notProvided),
                        TextEntry::make('last_name')
                            ->label(__('admin.profile.last_name'))
                            ->placeholder($notProvided),
                        TextEntry::make('email')
                            ->label(__('admin.profile.email'))
                            ->placeholder($notProvided),
                        TextEntry::make('phone_number')
                            ->label(__('admin.profile.phone'))
                            ->placeholder($notProvided),
                        TextEntry::make('type.name')
                            ->label(__('admin.resources.user.type'))
                            ->badge()
                            ->color(static fn (string $state): string => match ($state) {
                                'Admin' => 'danger',
                                'Vendor' => 'warning',
                                'Consumer' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('status.name')
                            ->label(__('admin.resources.user.status'))
                            ->badge()
                            ->color(static fn (string $state): string => match ($state) {
                                'Active' => 'success',
                                'Pending' => 'warning',
                                'Inactive', 'Deleted' => 'danger',
                                default => 'gray',
                            }),
                    ]),

                Section::make(__('admin.resources.vendor.profile_info'))
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('image')
                            ->label(__('admin.profile.avatar'))
                            ->disk('public')
                            ->circular()
                            ->imageSize(200)
                            ->defaultImageUrl(Storage::disk('public')->url('users/user.png')),

                        TextEntry::make('business_name')
                            ->label(__('admin.resources.vendor.business_name'))
                            ->placeholder($notProvided),
                        TextEntry::make('contact_phone')
                            ->label(__('admin.resources.vendor.contact_phone'))
                            ->placeholder($notProvided),
                        TextEntry::make('city')
                            ->label(__('admin.resources.vendor.city'))
                            ->placeholder($notProvided),
                        TextEntry::make('province')
                            ->label(__('admin.resources.vendor.province'))
                            ->placeholder($notProvided),
                        TextEntry::make('address')
                            ->label(__('admin.resources.vendor.address'))
                            ->columnSpanFull()
                            ->color('info')
                            ->placeholder($notProvided)
                            ->url(static fn (string $state): string => 'https://maps.google.com/?q='.urlencode($state))
                            ->openUrlInNewTab(),
                        IconEntry::make('is_verified')
                            ->label(__('admin.resources.vendor.verification_status'))
                            ->boolean(),
                    ]),

                Section::make(__('admin.resources.vendor.verification_docs'))
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('id_card_front')
                            ->label(__('admin.resources.vendor.id_card_front'))
                            ->placeholder($notProvided)
                            ->getStateUsing(static fn ($record) => $record->id_card_front
                                ? route('admin.documents.show', ['path' => $record->id_card_front]) : null
                            )
                            ->disk(null)
                            ->imageSize(200),
                        ImageEntry::make('id_card_back')
                            ->label(__('admin.resources.vendor.id_card_back'))
                            ->placeholder($notProvided)
                            ->getStateUsing(static fn ($record) => $record->id_card_back
                            ? route('admin.documents.show', ['path' => $record->id_card_back]) : null
                            )
                            ->disk(null)
                            ->imageSize(200),
                        ImageEntry::make('store_front_image')
                            ->label(__('admin.resources.vendor.store_photo'))
                            ->placeholder($notProvided)
                            ->getStateUsing(static fn ($record) => $record->store_front_image
                            ? route('admin.documents.show', ['path' => $record->store_front_image]) : null
                            )
                            ->disk(null)
                            ->imageSize(200),
                        TextEntry::make('organic_certificate_url')
                            ->label(__('admin.resources.vendor.organic_cert'))
                            ->placeholder($notProvided)
                            ->url(static fn (mixed $state) => $state
                            ? route('admin.documents.show', ['path' => $state]) : null
                            )
                            ->openUrlInNewTab()
                            ->color('primary'),
                    ]),

                Section::make(__('admin.resources.vendor.financial_details'))
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('bank_name')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.vendor.bank_name')),
                        TextEntry::make('bank_account_name')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.vendor.account_holder')),
                        TextEntry::make('bank_account_number')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.vendor.account_number')),
                        ImageEntry::make('bank_qr_code')
                            ->placeholder($notProvided)
                            ->label(__('admin.resources.vendor.qr_code'))
                            ->getStateUsing(static fn ($record) => $record->bank_qr_code
                            ? route('admin.documents.show', ['path' => $record->bank_qr_code]) : null
                            )
                            ->disk(null)
                            ->imageSize(200),
                    ]),

                Section::make(__('admin.resources.vendor.wallets_info'))
                    ->schema([
                        RepeatableEntry::make('wallets')
                            ->label(__('admin.resources.vendor.wallets_info'))
                            ->schema([
                                TextEntry::make('currency.name')
                                    ->placeholder($notProvided)
                                    ->label(__('admin.resources.wallet.currency')),
                                TextEntry::make('balance')
                                    ->placeholder($notProvided)
                                    ->label(__('admin.resources.wallet.balance'))
                                    ->getStateUsing(static function (Wallet $record): string {
                                        $id = $record->currency?->id;
                                        $symbol = $record->currency->symbol ?? '';
                                        $balance = number_format((float) $record->balance, 2);

                                        return $id === Currency::USD_ID
                                            ? "{$symbol} {$balance}"
                                            : "{$balance} {$symbol}";
                                    }),
                            ])
                            ->columns(2),
                    ]),

                Section::make(__('admin.resources.vendor.system_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->placeholder($notProvided)
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->placeholder($notProvided)
                            ->dateTime('d M Y, h:i A'),
                    ]),
            ]);
    }
}
