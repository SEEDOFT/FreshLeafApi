<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Schemas;

use App\Models\PaymentMethod;
use App\Models\PaymentMethodStatus;
use App\Models\UserStatus;
use App\Models\UserType;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.vendor.account_info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.first_name').'</strong>'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        TextInput::make('last_name')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.last_name').'</strong>'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        TextInput::make('email')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.email').'</strong>'))
                            ->email(),
                        TextInput::make('phone_number')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.phone').'</strong>'))
                            ->tel()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('user_type_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.account_type').'</strong>'))
                            ->relationship('type')
                            ->getOptionLabelFromRecordUsing(fn (UserType $record) => $record->translated_name)
                            ->default(UserType::VENDOR_ID)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),
                        Select::make('user_status_id')
                            ->label(new HtmlString('<strong>'.__('admin.resources.user.account_status').'</strong>'))
                            ->relationship('status')
                            ->getOptionLabelFromRecordUsing(fn (UserStatus $record) => $record->translated_name)
                            ->default(UserStatus::ACTIVE_ID)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),
                    ]),

                Section::make(__('admin.resources.vendor.business_profile'))
                    ->relationship('vendorProfile')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('business_name')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.business_name').'</strong>'))
                                    ->required(fn (string $operation): bool => $operation === 'create'),
                                TextInput::make('contact_phone')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.contact_phone').'</strong>'))
                                    ->required(fn (string $operation): bool => $operation === 'create'),
                            ]),
                        Toggle::make('is_verified')
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.verified').'</strong>'))
                            ->disabled()
                            ->default(true),
                    ]),

                Section::make(__('admin.resources.vendor.addresses'))
                    ->schema([
                        Repeater::make('addresses')
                            ->relationship('addresses')
                            ->schema([
                                TextInput::make('label')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.address_label').'</strong>'))
                                    ->required(),
                                TextInput::make('recipient_name')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.recipient_name').'</strong>'))
                                    ->required(),
                                TextInput::make('phone')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.contact_phone').'</strong>'))
                                    ->tel()
                                    ->required(),
                                TextInput::make('address_line_1')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.address_line_1').'</strong>'))
                                    ->required(),
                                TextInput::make('address_line_2')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.address_line_2').'</strong>')),
                                TextInput::make('city')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.city').'</strong>'))
                                    ->required(),
                                TextInput::make('province')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.province').'</strong>'))
                                    ->required(),
                                TextInput::make('postal_code')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.postal_code').'</strong>'))
                                    ->required(),
                                Hidden::make('lat'),
                                Hidden::make('long'),
                                Map::make('location')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.location').'</strong>'))
                                    ->columnSpanFull()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?array $state): void {
                                        if (! $state) {
                                            return;
                                        }
                                        $set('lat', $state['lat'] ?? null);
                                        $set('long', $state['lng'] ?? null);

                                        $response = Http::withHeaders([
                                            'User-Agent' => 'FreshLeafApp/1.0',
                                        ])->get('https://nominatim.openstreetmap.org/reverse', [
                                            'lat' => $state['lat'],
                                            'lon' => $state['lng'],
                                            'format' => 'json',
                                            'accept-language' => 'en',
                                        ]);

                                        if ($response->successful()) {
                                            $data = $response->json();
                                            $address = $data['address'] ?? [];

                                            if (empty($get('address_line_1'))) {
                                                $set('address_line_1', $data['display_name'] ?? null);
                                            }
                                            if (empty($get('city'))) {
                                                $set('city', $address['city'] ?? $address['town'] ?? $address['village'] ?? null);
                                            }
                                            if (empty($get('province'))) {
                                                $set('province', $address['state'] ?? $address['province'] ?? null);
                                            }
                                            if (empty($get('postal_code'))) {
                                                $set('postal_code', $address['postcode'] ?? null);
                                            }
                                        }
                                    })
                                    ->afterStateHydrated(function (Set $set, $state, $record) {
                                        if ($record) {
                                            $set('lat', $record->lat);
                                            $set('long', $record->long);
                                        }
                                    }),
                            ])
                            ->columns(2),
                    ]),

                Section::make(__('admin.resources.vendor.identity_verification'))
                    ->relationship('vendorProfile')
                    ->schema([
                        FileUpload::make('id_card_front')
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.id_card_front').'</strong>'))
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        FileUpload::make('id_card_back')
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.id_card_back').'</strong>'))
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        FileUpload::make('store_front_image')
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.store_photo').'</strong>'))
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        FileUpload::make('organic_certificate_url')
                            ->label(new HtmlString('<strong>'.__('admin.resources.vendor.organic_cert').'</strong>'))
                            ->disk('local')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->directory('vendor-verification'),
                    ])->columns(2),

                Section::make(__('admin.resources.vendor.payment_methods'))
                    ->schema([
                        Repeater::make('paymentMethods')
                            ->relationship('paymentMethods')
                            ->schema([
                                Select::make('payment_method_type_id')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.payment_method_type').'</strong>'))
                                    ->relationship('type', 'name_en')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ((int) $state === PaymentMethod::ABA_ID) {
                                            $set('bank_name', 'ABA');
                                        } elseif ((int) $state === PaymentMethod::ACLEDA_ID) {
                                            $set('bank_name', 'Acleda');
                                        }
                                    })
                                    ->columnSpanFull(),
                                TextInput::make('bank_name')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.bank_name').'</strong>'))
                                    ->visible(fn (Get $get) => ! in_array((int) $get('payment_method_type_id'), [PaymentMethod::ABA_ID, PaymentMethod::ACLEDA_ID]))
                                    ->required(),
                                TextInput::make('account_name')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.account_holder').'</strong>'))
                                    ->required(),
                                TextInput::make('account_number')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.account_number').'</strong>'))
                                    ->required(),
                                FileUpload::make('qr_code')
                                    ->label(new HtmlString('<strong>'.__('admin.resources.vendor.qr_code').'</strong>'))
                                    ->image()
                                    ->disk('local')
                                    ->directory('vendor-verification')
                                    ->columnSpanFull(),
                                Hidden::make('payment_method_status_id')
                                    ->default(PaymentMethodStatus::ACTIVE_ID),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}
