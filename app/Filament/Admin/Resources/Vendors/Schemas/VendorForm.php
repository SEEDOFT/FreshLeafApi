<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Schemas;

use App\Constants\StorageDirectory;
use App\Filament\Forms\Components\PasswordInput;
use App\Filament\Forms\Components\PhoneNumberInput;
use App\Models\PaymentMethodStatus;
use App\Models\PaymentMethodType;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

use function filled;
use function ltrim;
use function preg_replace;
use function str_starts_with;

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
                            ->label(__('admin.resources.user.first_name'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        TextInput::make('last_name')
                            ->label(__('admin.resources.user.last_name'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        TextInput::make('email')
                            ->label(__('admin.resources.user.email'))
                            ->email()
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        PhoneNumberInput::make('phone_number')
                            ->label(__('admin.resources.user.phone'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (PhoneNumberInput $component, Component $livewire, ?string $state, ?Model $record): void {
                                if (blank($state)) {
                                    $livewire->resetValidation($component->getStatePath());

                                    return;
                                }
                                $cleaned = (string) preg_replace('/\s+/', '', $state);
                                $formattedPhone = str_starts_with($cleaned, '+855')
                                    ? $cleaned
                                    : '+855'.ltrim($cleaned, '0');
                                $query = User::where('phone_number', $formattedPhone)
                                    ->where('user_type_id', UserType::VENDOR_ID)
                                    ->whereNull('deleted_at');
                                if ($record) {
                                    $query->where('id', '!=', $record->getKey());
                                }
                                if ($query->exists()) {
                                    $livewire->addError($component->getStatePath(), __('shared.auth.register.phone_registered'));
                                } else {
                                    $livewire->resetValidation($component->getStatePath());
                                }
                            })
                            ->rule(function (?Model $record) {
                                return function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                    $cleaned = (string) preg_replace('/\s+/', '', (string) $value);
                                    $formattedPhone = str_starts_with($cleaned, '+855')
                                        ? $cleaned
                                        : '+855'.ltrim($cleaned, '0');

                                    $query = User::where('phone_number', $formattedPhone)
                                        ->where('user_type_id', UserType::VENDOR_ID)
                                        ->whereIn('user_status_id', [
                                            UserStatus::ACTIVE_ID,
                                            UserStatus::PENDING_ID,
                                            UserStatus::INACTIVE_ID,
                                        ])
                                        ->whereNull('deleted_at');

                                    if ($record) {
                                        $query->where('id', '!=', $record->getKey());
                                    }

                                    if ($query->exists()) {
                                        $fail(__('shared.auth.register.phone_registered'));
                                    }
                                };
                            }),
                        PasswordInput::make('password')
                            ->label(__('shared.auth.login.password'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->revealable(),
                        PasswordInput::make('password_confirmation')
                            ->label(__('shared.auth.register.password_confirm'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->revealable(),
                        Select::make('user_type_id')
                            ->label(__('admin.resources.user.type'))
                            ->options(
                                UserType::all()
                                    ->pluck('translated_name', 'id')
                            )
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->default(UserType::VENDOR_ID)
                            ->disabled()
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('user_status_id')
                            ->label(__('admin.resources.user.status'))
                            ->options(
                                UserStatus::where('id', '!=', UserStatus::DELETED_ID)
                                    ->get()
                                    ->pluck('translated_name', 'id')
                            )
                            ->default(UserStatus::ACTIVE_ID)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                    ]),

                Section::make(__('admin.resources.vendor.business_profile'))
                    ->relationship('vendorProfile')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('business_name')
                                    ->label(__('admin.resources.vendor.business_name'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->maxLength(255)
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                PhoneNumberInput::make('contact_phone')
                                    ->label(__('shared.form.fields.contact_phone'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                TextInput::make('village')
                                    ->label(__('shared.form.fields.village'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->maxLength(255)
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                TextInput::make('commune')
                                    ->label(__('shared.form.fields.commune'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->maxLength(255)
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                TextInput::make('district')
                                    ->label(__('shared.form.fields.district'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->maxLength(255)
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                TextInput::make('province')
                                    ->label(__('shared.form.fields.province'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->maxLength(255)
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                            ]),
                        Hidden::make('is_verified')
                            ->label(__('admin.resources.vendor.verified'))
                            ->dehydrated()
                            ->default(true),
                    ]),

                Section::make(__('admin.resources.vendor.identity_verification'))
                    ->relationship('vendorProfile')
                    ->schema([
                        FileUpload::make('id_card_front')
                            ->label(__('admin.resources.vendor.id_card_front'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(6144)
                            ->disk('local')
                            ->directory(StorageDirectory::VENDOR_VERIFICATION)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        FileUpload::make('id_card_back')
                            ->label(__('admin.resources.vendor.id_card_back'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(6144)
                            ->disk('local')
                            ->directory(StorageDirectory::VENDOR_VERIFICATION)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        FileUpload::make('store_front_image')
                            ->label(__('admin.resources.vendor.store_photo'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(6144)
                            ->disk('public')
                            ->directory(StorageDirectory::SHOPS)
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        FileUpload::make('organic_certificate_url')
                            ->label(__('admin.resources.vendor.organic_cert'))
                            ->maxSize(6144)
                            ->disk('local')
                            ->directory(StorageDirectory::VENDOR_VERIFICATION)
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                    ])->columns(2),

                Section::make(__('admin.resources.vendor.payment_methods'))
                    ->relationship('vendorFinancialDetails')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('payment_method_type_id')
                                ->label(__('admin.resources.vendor.bank_name'))
                                ->options(
                                    PaymentMethodType::whereIn('id', [
                                        PaymentMethodType::ABA_ID,
                                        PaymentMethodType::ACLEDA_ID,
                                    ])->pluck('name_en', 'id')
                                )
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->live()
                                ->dehydrated(fn (mixed $state): bool => filled($state))
                                ->afterStateUpdated(function (Set $set, ?string $state): void {
                                    if ($state) {
                                        $type = PaymentMethodType::find((int) $state);
                                        $set('bank_name', $type?->name_en);
                                    }
                                }),
                            TextInput::make('account_name')
                                ->label(__('admin.resources.vendor.account_holder'))
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->maxLength(255)
                                ->dehydrated(fn (mixed $state): bool => filled($state)),
                            TextInput::make('account_number')
                                ->label(__('admin.resources.vendor.account_number'))
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->maxLength(255)
                                ->dehydrated(fn (mixed $state): bool => filled($state)),
                        ]),
                        FileUpload::make('qr_code')
                            ->label(__('admin.resources.vendor.qr_code'))
                            ->image()
                            ->maxSize(6144)
                            ->disk('public')
                            ->directory(StorageDirectory::SHOPS)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        Hidden::make('bank_name')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        Hidden::make('payment_method_status_id')
                            ->default(PaymentMethodStatus::ACTIVE_ID)
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                    ]),
            ]);
    }
}
