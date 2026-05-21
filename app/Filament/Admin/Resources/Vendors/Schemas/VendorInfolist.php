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
use Illuminate\Support\HtmlString;

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
                                    ->label(new HtmlString('<strong>'.__('admin.profile.avatar').'</strong>'))
                                    ->circular()
                                    ->imageSize(200)
                                    ->alignCenter(),
                            ]),
                        TextEntry::make('first_name')
                            ->label(new HtmlString('<strong>'.__('admin.profile.first_name').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('last_name')
                            ->label(new HtmlString('<strong>'.__('admin.profile.last_name').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('email')
                            ->label(new HtmlString('<strong>'.__('admin.profile.email').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('phone_number')
                            ->label(new HtmlString('<strong>'.__('admin.profile.phone').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('type.translated_name')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.type').'</strong>'))
                            ->placeholder($notProvided)
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('status.translated_name')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.status').'</strong>'))
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
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.business_name').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('contact_phone')
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.contact_phone').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('village')
                            ->label(new HtmlString('<strong>'.__('shared.form.fields.village').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('commune')
                            ->label(new HtmlString('<strong>'.__('shared.form.fields.commune').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('district')
                            ->label(new HtmlString('<strong>'.__('shared.form.fields.district').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('province')
                            ->label(new HtmlString('<strong>'.__('shared.form.fields.province').'</strong>'))
                            ->placeholder($notProvided),
                        TextEntry::make('address')
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.address').'</strong>'))
                            ->columnSpanFull()
                            ->placeholder($notProvided),
                        TextEntry::make('opening_time')
                            ->label(new HtmlString('<strong>'.__('vendor.settings.business_profile.opening_time').'</strong>'))
                            ->placeholder($notProvided)
                            ->dateTime('h:i A'),
                        TextEntry::make('closing_time')
                            ->label(new HtmlString('<strong>'.__('vendor.settings.business_profile.closing_time').'</strong>'))
                            ->placeholder($notProvided)
                            ->dateTime('h:i A'),
                        TextEntry::make('is_open')
                            ->label(new HtmlString('<strong>'.__('vendor.settings.business_profile.is_open').'</strong>'))
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Now Is Open' : 'Now Is Closed')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->disabled(),
                        IconEntry::make('is_verified')
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.verification_status').'</strong>'))
                            ->boolean(),
                    ]),

                Section::make(__('admin.resources.vendor.verification_docs'))
                    ->relationship('vendorProfile')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('id_card_front')
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.id_card_front').'</strong>'))
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
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.id_card_back').'</strong>'))
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
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.store_photo').'</strong>'))
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
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.organic_cert').'</strong>'))
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
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.bank_name').'</strong>')),
                                TextEntry::make('account_name')
                                    ->placeholder($notProvided)
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.account_holder').'</strong>')),
                                TextEntry::make('account_number')
                                    ->placeholder($notProvided)
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.account_number').'</strong>')),
                            ]),
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                ImageEntry::make('qr_code')
                                    ->placeholder($notProvided)
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.qr_code').'</strong>'))
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
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.wallets_info').'</strong>'))
                            ->schema([
                                TextEntry::make('currency.translated_currency')
                                    ->placeholder($notProvided)
                                    ->label(new HtmlString('<strong>'.__('admin.resources.wallet.currency').'</strong>')),
                                TextEntry::make('balance')
                                    ->placeholder($notProvided)
                                    ->label(new HtmlString('<strong>'.__('admin.resources.wallet.balance').'</strong>'))
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
                            ->label(new HtmlString('<strong>'.__('admin.resources.created_at').'</strong>'))
                            ->placeholder($notProvided)
                            ->dateTime('d M Y, h:i A'),
                        TextEntry::make('updated_at')
                            ->label(new HtmlString('<strong>'.__('admin.resources.updated_at').'</strong>'))
                            ->placeholder($notProvided)
                            ->dateTime('d M Y, h:i A'),
                    ]),
            ]);
    }
}
