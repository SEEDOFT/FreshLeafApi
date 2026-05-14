<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages\Auth;

use App\Filament\Forms\Components\PasswordInput;
use App\Filament\Forms\Components\PhoneNumberInput;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Closure;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Override;

class Register extends BaseRegister
{
    #[Override]
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.base';
    }

    protected string $view = 'filament.vendor.pages.auth.register';

    #[Override]
    public function getHeading(): string|Htmlable
    {
        return __('admin.auth.register.title');
    }

    #[Override]
    public function getSubHeading(): string|Htmlable|null
    {
        return new HtmlString(
            __('admin.auth.register.subheading').' '.
            __('admin.auth.register.already_have_account').' '.
            '<a class="text-primary-600 font-medium hover:text-primary-500" href="'.route('filament.vendor.auth.login').'">'.
            __('admin.auth.register.login_here').
            '</a>'
        );
    }

    #[Override]
    public function getMaxWidth(): string
    {
        return '4xl';
    }

    #[Override]
    protected function getFormActions(): array
    {
        return []; // Hide default buttons
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make(__('admin.auth.register.steps.account'))
                        ->icon('heroicon-o-user')
                        ->description(__('admin.auth.register.steps.account_desc'))
                        ->schema([
                            Grid::make(1)->schema([
                                $this->getNameFormComponent(),
                                $this->getPhoneNumberFormComponent(),
                                PasswordInput::make('password')
                                    ->label(__('admin.auth.login.password'))
                                    ->required()
                                    ->revealable(),
                                PasswordInput::make('password_confirmation')
                                    ->label(__('admin.auth.register.password_confirm'))
                                    ->required()
                                    ->revealable(),
                            ]),
                        ]),

                    Step::make(__('admin.auth.register.steps.business'))
                        ->icon('heroicon-o-building-storefront')
                        ->description(__('admin.auth.register.steps.business_desc'))
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('business_name')
                                    ->label(__('admin.auth.register.business_name'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->maxLength(255),
                                TextInput::make('contact_phone')
                                    ->label(__('panels.form.fields.contact_phone'))
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('city')
                                    ->label(__('panels.form.fields.city'))
                                    ->maxLength(255),
                                TextInput::make('province')
                                    ->label(__('panels.form.fields.province'))
                                    ->maxLength(255),
                            ]),
                            TextInput::make('address')
                                ->label(__('panels.form.fields.address'))
                                ->columnSpanFull()
                                ->maxLength(255),
                        ]),

                    Step::make(__('admin.auth.register.steps.verification'))
                        ->icon('heroicon-o-shield-check')
                        ->description(__('admin.auth.register.steps.verification_desc'))
                        ->schema([
                            Grid::make(2)->schema([
                                FileUpload::make('id_card_front')
                                    ->label(__('admin.auth.register.id_front'))
                                    ->image()
                                    ->disk('local')
                                    ->directory('vendor-verification')
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                FileUpload::make('id_card_back')
                                    ->label(__('admin.auth.register.id_back'))
                                    ->image()
                                    ->disk('local')
                                    ->directory('vendor-verification')
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                FileUpload::make('store_front_image')
                                    ->label(__('admin.auth.register.store_photo'))
                                    ->image()
                                    ->disk('local')
                                    ->directory('vendor-verification')
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                FileUpload::make('organic_certificate_url')
                                    ->label(__('admin.auth.register.organic_cert'))
                                    ->disk('local')
                                    ->directory('vendor-verification'),
                            ]),
                        ]),

                    Step::make(__('admin.auth.register.steps.financials'))
                        ->icon('heroicon-o-banknotes')
                        ->description(__('admin.auth.register.steps.financials_desc'))
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('bank_name')
                                    ->label(__('admin.auth.register.bank_name'))
                                    ->placeholder('e.g. ABA Bank')
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->maxLength(255),
                                TextInput::make('bank_account_name')
                                    ->label(__('admin.auth.register.account_holder'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->maxLength(255),
                                TextInput::make('bank_account_number')
                                    ->label(__('admin.auth.register.account_number'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->maxLength(255),
                            ]),
                            FileUpload::make('bank_qr_code')
                                ->label(__('admin.auth.register.qr_code'))
                                ->image()
                                ->disk('local')
                                ->directory('vendor-verification')
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->dehydrated(fn (mixed $state): bool => filled($state)),
                        ]),
                ])
                    ->submitAction(
                        new HtmlString(
                            Blade::render(
                                '<x-filament::button type="submit" size="sm" wire:click="register">'
                                .__('admin.auth.register.complete')
                                .'</x-filament::button>'
                            )
                        ),
                    ),
            ])
            ->statePath('data');
    }

    protected function getPhoneNumberFormComponent(): Grid
    {
        return Grid::make(5)
            ->schema([
                PhoneNumberInput::make('phone_number')
                    ->label(__('admin.auth.login.phone'))
                    ->required()
                    ->columnSpanFull()
                    ->rule(static function (Get $get) {
                        return static function (string $attribute, $value, Closure $fail) use ($get) {
                            $dialCode = get_dial_code($get('country_iso'));
                            $fullPhone = $dialCode.ltrim($value, '0');
                            $exists = User::where('phone_number', $fullPhone)
                                ->where('user_type_id', UserType::VENDOR)
                                ->whereIn('user_status_id', [
                                    UserStatus::ACTIVE_ID,
                                    UserStatus::PENDING_ID,
                                    UserStatus::INACTIVE_ID,
                                ])
                                ->whereNull('deleted_at')
                                ->exists();

                            if ($exists) {
                                $fail('This phone number is already registered.');
                            }
                        };
                    }),
            ]);
    }

    #[Override]
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $nameParts = explode(' ', $data['name'], 2);
        $data['first_name'] = $nameParts[0];
        $data['last_name'] = $nameParts[1] ?? '';

        unset($data['name']);

        $dialCode = get_dial_code($data['country_iso']);
        $phoneInput = preg_replace('/[^0-9]/', '', $data['phone_number_input'] ?? '');
        $data['phone_number'] = $dialCode.ltrim($phoneInput, '0');

        unset($data['country_iso'], $data['phone_number_input']);

        $data['user_type_id'] = UserType::VENDOR_ID;
        $data['user_status_id'] = UserStatus::PENDING_ID;

        return $data;
    }

    #[Override]
    protected function handleRegistration(array $data): Model
    {
        // 1. Separate vendor profile data
        $vendorData = Arr::only($data, [
            'business_name', 'contact_phone', 'city', 'province', 'address',
            'id_card_front', 'id_card_back', 'store_front_image', 'organic_certificate_url',
            'bank_name', 'bank_account_name', 'bank_account_number', 'bank_qr_code',
        ]);

        // 2. Separate user data
        $userData = Arr::except($data, array_keys($vendorData));

        // 3. Create User
        /** @var User $user */
        $user = $this->getUserModel()::create($userData);

        // 4. Create Vendor Profile
        $user->vendorProfile()->create($vendorData);

        // 5. Create Default Wallets (0.00 Balance)
        $user->ensureDefaultWallets();

        return $user;
    }

    protected function afterRegister(): void
    {
        Auth::logout();
        session()->invalidate();
        $this->redirect(route('filament.vendor.auth.login'));
    }

    #[Override]
    protected function isRegisterRateLimited(string $email): bool
    {
        $phone = $this->data['phone_number'] ?? '';

        return parent::isRegisterRateLimited($phone);
    }
}
