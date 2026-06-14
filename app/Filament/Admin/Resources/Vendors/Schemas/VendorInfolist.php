<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Schemas;

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
                                    ->getStateUsing(fn ($record) => resolve_image_url($record->image))
                                    ->circular()

                                    ->alignCenter()
                                    ->extraImgAttributes(fn () => [
                                        'class' => 'cursor-zoom-in',
                                        'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                                    ]),
                            ]),
                        TextEntry::make('first_name')
                            ->label(__('admin.profile.first_name')),
                        TextEntry::make('last_name')
                            ->label(__('admin.profile.last_name')),
                        TextEntry::make('email')
                            ->label(__('admin.profile.email')),
                        TextEntry::make('phone_number')
                            ->label(__('admin.profile.phone')),
                        TextEntry::make('type.translated_name')
                            ->label(__('admin.resources.user.type'))
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('status.translated_name')
                            ->label(__('admin.resources.user.status'))
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
                            ->label(__('admin.resources.vendor.business_name')),
                        TextEntry::make('contact_phone')
                            ->label(__('admin.resources.vendor.contact_phone')),
                        TextEntry::make('village')
                            ->label(__('shared.form.fields.village')),
                        TextEntry::make('commune')
                            ->label(__('shared.form.fields.commune')),
                        TextEntry::make('district')
                            ->label(__('shared.form.fields.district')),
                        TextEntry::make('province')
                            ->label(__('shared.form.fields.province')),
                        TextEntry::make('address')
                            ->label(__('admin.resources.vendor.address'))
                            ->columnSpanFull(),
                        TextEntry::make('opening_time')
                            ->label(__('vendor.settings.business_profile.opening_time'))
                            ->dateTime('h:i A'),
                        TextEntry::make('closing_time')
                            ->label(__('vendor.settings.business_profile.closing_time'))
                            ->dateTime('h:i A'),
                        TextEntry::make('is_open')
                            ->label(__('vendor.settings.business_profile.is_open'))
                            ->formatStateUsing(fn (bool $state): string => $state
                                    ? __('vendor.settings.business_profile.is_open_label')
                                    : __('vendor.settings.business_profile.is_closed_label')
                            )
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
                            ->getStateUsing(fn ($record) => $record->id_card_front
                                ? route('admin.documents.show', ['path' => $record->id_card_front]) : null
                            )
                            ->disk(null)

                            ->extraImgAttributes(fn () => [
                                'class' => 'cursor-zoom-in',
                                'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                            ]),
                        ImageEntry::make('id_card_back')
                            ->label(__('admin.resources.vendor.id_card_back'))
                            ->getStateUsing(fn ($record) => $record->id_card_back
                                ? route('admin.documents.show', ['path' => $record->id_card_back]) : null
                            )
                            ->disk(null)

                            ->extraImgAttributes(fn () => [
                                'class' => 'cursor-zoom-in',
                                'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                            ]),
                        ImageEntry::make('store_front_image')
                            ->label(__('admin.resources.vendor.store_photo'))
                            ->getStateUsing(fn ($record) => resolve_image_url($record->store_front_image))
                            ->disk(null)

                            ->extraImgAttributes(fn () => [
                                'class' => 'cursor-zoom-in',
                                'x-on:click' => "\$dispatch('lightbox', { src: \$el.src })",
                            ]),
                        TextEntry::make('organic_certificate_url')
                            ->label(__('admin.resources.vendor.organic_cert'))
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
                                    ->label(__('admin.resources.vendor.bank_name')),
                                TextEntry::make('account_name')
                                    ->label(__('admin.resources.vendor.account_holder')),
                                TextEntry::make('account_number')
                                    ->label(__('admin.resources.vendor.account_number')),
                            ]),
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                ImageEntry::make('qr_code')
                                    ->label(__('admin.resources.vendor.qr_code'))
                                    ->getStateUsing(fn (?PaymentMethod $record) => resolve_image_url($record?->qr_code))
                                    ->disk(null)
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
                                    ->label(__('admin.resources.wallet.currency')),
                                TextEntry::make('balance')
                                    ->label(__('admin.resources.wallet.balance'))
                                    ->getStateUsing(fn (Wallet $record): string => format_currency(
                                        $record->balance,
                                        $record->currency->code
                                    )),
                            ])
                            ->columns(2),
                    ]),

                Section::make(__('admin.resources.vendor.system_info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('admin.resources.created_at'))
                            ->dateTime('h:i A, d M Y'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.resources.updated_at'))
                            ->dateTime('h:i A, d M Y'),
                    ]),
            ]);
    }
}
