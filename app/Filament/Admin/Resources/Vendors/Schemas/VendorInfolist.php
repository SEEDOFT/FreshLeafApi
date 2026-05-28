<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Schemas;

use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\Wallet;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                        Grid::make(1)
                            ->columnSpanFull()
                            ->schema([
                                ImageEntry::make('image')
                                    ->label(__('admin.profile.avatar'))
                                    ->circular()
                                    ->imageSize(200)
                                    ->alignCenter(),
                            ]),
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
                        TextEntry::make('type.translated_name')
                            ->label(__('admin.resources.user.type'))
                            ->placeholder($notProvided)
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('status.translated_name')
                            ->label(__('admin.resources.user.status'))
                            ->placeholder($notProvided)
                            ->badge()
                            ->color(fn (User $record): string => match ($record->user_status_id) {
                                UserStatus::ACTIVE_ID => 'success',
                                UserStatus::PENDING_ID => 'warning',
                                UserStatus::INACTIVE_ID, UserStatus::REJECTED_ID, UserStatus::DELETED_ID => 'danger',
                                default => 'gray',
                            }),
                    ]),

                Section::make(__('admin.resources.vendor.profile_info'))
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('business_name')
                            ->label(__('admin.resources.vendor.business_name'))
                            ->placeholder($notProvided),
                        TextEntry::make('contact_phone')
                            ->label(__('admin.resources.vendor.contact_phone'))
                            ->placeholder($notProvided),
                        TextEntry::make('village')
                            ->label(__('shared.form.fields.village'))
                            ->placeholder($notProvided),
                        TextEntry::make('commune')
                            ->label(__('shared.form.fields.commune'))
                            ->placeholder($notProvided),
                        TextEntry::make('district')
                            ->label(__('shared.form.fields.district'))
                            ->placeholder($notProvided),
                        TextEntry::make('province')
                            ->label(__('shared.form.fields.province'))
                            ->placeholder($notProvided),
                        TextEntry::make('address')
                            ->label(__('admin.resources.vendor.address'))
                            ->columnSpanFull()
                            ->placeholder($notProvided),
                        TextEntry::make('opening_time')
                            ->label(__('vendor.settings.business_profile.opening_time'))
                            ->placeholder($notProvided)
                            ->dateTime('h:i A'),
                        TextEntry::make('closing_time')
                            ->label(__('vendor.settings.business_profile.closing_time'))
                            ->placeholder($notProvided)
                            ->dateTime('h:i A'),
                        TextEntry::make('is_open')
                            ->label(__('vendor.settings.business_profile.is_open'))
                            ->formatStateUsing(fn (bool $state): string => $state ? __('vendor.settings.business_profile.is_open_label') : __('vendor.settings.business_profile.is_closed_label'))
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->disabled(),
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
                            ->getStateUsing(fn ($record) => $record->id_card_front
                                ? route('admin.documents.show', ['path' => $record->id_card_front]) : null
                            )
                            ->disk(null)
                            ->imageSize(200)
                            ->extraImgAttributes(fn () => [
                                'class' => 'cursor-zoom-in',
                                'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                            ]),
                        ImageEntry::make('id_card_back')
                            ->label(__('admin.resources.vendor.id_card_back'))
                            ->placeholder($notProvided)
                            ->getStateUsing(fn ($record) => $record->id_card_back
                                ? route('admin.documents.show', ['path' => $record->id_card_back]) : null
                            )
                            ->disk(null)
                            ->imageSize(200)
                            ->extraImgAttributes(fn () => [
                                'class' => 'cursor-zoom-in',
                                'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                            ]),
                        ImageEntry::make('store_front_image')
                            ->label(__('admin.resources.vendor.store_photo'))
                            ->placeholder($notProvided)
                            ->getStateUsing(fn ($record) => $record->store_front_image
                                ? route('admin.documents.show', ['path' => $record->store_front_image]) : null
                            )
                            ->disk(null)
                            ->imageSize(200)
                            ->extraImgAttributes(fn () => [
                                'class' => 'cursor-zoom-in',
                                'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                            ]),
                        TextEntry::make('organic_certificate_url')
                            ->label(__('admin.resources.vendor.organic_cert'))
                            ->placeholder($notProvided)
                            ->url(fn (mixed $state) => $state
                                ? route('admin.documents.show', ['path' => $state]) : null
                            )
                            ->openUrlInNewTab()
                            ->color('primary'),
                    ]),

                Section::make(__('admin.resources.vendor.financial_details'))
                    ->relationship('vendorFinancialDetails')
                    ->columns(2)
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('bank_name')
                                    ->placeholder($notProvided)
                                    ->label(__('admin.resources.vendor.bank_name')),
                                TextEntry::make('account_name')
                                    ->placeholder($notProvided)
                                    ->label(__('admin.resources.vendor.account_holder')),
                                TextEntry::make('account_number')
                                    ->placeholder($notProvided)
                                    ->label(__('admin.resources.vendor.account_number')),
                            ]),
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                ImageEntry::make('qr_code')
                                    ->placeholder($notProvided)
                                    ->label(__('admin.resources.vendor.qr_code'))
                                    ->getStateUsing(fn (PaymentMethod $record) => $record->qr_code
                                        ? route('admin.documents.show', ['path' => $record->qr_code]) : null
                                    )
                                    ->disk(null)
                                    ->imageSize(200)
                                    ->extraImgAttributes(fn () => [
                                        'class' => 'cursor-zoom-in',
                                        'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                                    ]),
                            ]),
                    ]),

                Section::make(__('admin.resources.vendor.wallets_info'))
                    ->schema([
                        RepeatableEntry::make('wallets')
                            ->label(__('admin.resources.vendor.wallets_info'))
                            ->schema([
                                TextEntry::make('currency.translated_currency')
                                    ->placeholder($notProvided)
                                    ->label(__('admin.resources.wallet.currency')),
                                TextEntry::make('balance')
                                    ->placeholder($notProvided)
                                    ->label(__('admin.resources.wallet.balance'))
                                    ->getStateUsing(function (Wallet $record): string {
                                        $id = $record->currency->id;
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
