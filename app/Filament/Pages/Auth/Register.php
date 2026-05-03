<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Override;

class Register extends BaseRegister
{
    #[Override]
    public function getHeading(): string|Htmlable
    {
        return 'Create vendor account';
    }

    #[Override]
    public function getSubHeading(): string|Htmlable|null
    {
        return 'Complete your business profile to start selling.';
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
                    Step::make('Account')
                        ->icon('heroicon-o-user')
                        ->description('Basic information for login.')
                        ->schema([
                            Grid::make(2)->schema([
                                $this->getNameFormComponent(),
                                $this->getPhoneNumberFormComponent(),
                                $this->getPasswordFormComponent(),
                                $this->getPasswordConfirmationFormComponent(),
                            ]),
                        ]),

                    Step::make('Business')
                        ->icon('heroicon-o-building-storefront')
                        ->description('Information about your store or farm.')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('business_name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('contact_phone')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('city')
                                    ->maxLength(255),
                                TextInput::make('province')
                                    ->maxLength(255),
                            ]),
                            TextInput::make('address')
                                ->columnSpanFull()
                                ->maxLength(255),
                        ]),

                    Step::make('Verification')
                        ->icon('heroicon-o-shield-check')
                        ->description('Required identity documents.')
                        ->schema([
                            Grid::make(2)->schema([
                                FileUpload::make('id_card_front')
                                    ->label('ID Card (Front)')
                                    ->image()
                                    ->directory('vendor-verification')
                                    ->required(),
                                FileUpload::make('id_card_back')
                                    ->label('ID Card (Back)')
                                    ->image()
                                    ->directory('vendor-verification')
                                    ->required(),
                                FileUpload::make('store_front_image')
                                    ->label('Farm / Store Photo')
                                    ->image()
                                    ->directory('vendor-verification')
                                    ->required(),
                                FileUpload::make('organic_certificate_url')
                                    ->label('Organic Certificate (Optional)')
                                    ->directory('vendor-verification'),
                            ]),
                        ]),

                    Step::make('Financials')
                        ->icon('heroicon-o-banknotes')
                        ->description('Payout account details.')
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('bank_name')
                                    ->label('Bank Name')
                                    ->placeholder('e.g. ABA Bank')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('bank_account_name')
                                    ->label('Account Holder Name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('bank_account_number')
                                    ->label('Account Number')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                            FileUpload::make('bank_qr_code')
                                ->label('Bank QR Code')
                                ->image()
                                ->directory('vendor-verification')
                                ->required(),
                        ]),
                ])->submitAction(new HtmlString(Blade::render('<x-filament::button type="submit" size="sm" wire:click="register">Complete Registration</x-filament::button>'))),
            ])->statePath('data');
    }

    protected function getPhoneNumberFormComponent(): Grid
    {
        return Grid::make(4)
            ->schema([
                Select::make('country_iso')
                    ->label('Country')
                    ->options(get_country_options())
                    ->default('KH')
                    ->required()
                    ->searchable()
                    ->columnSpan(2),
                TextInput::make('phone_number_input')
                    ->label(__('custom.phone_number'))
                    ->placeholder('12 345 678')
                    ->required()
                    ->tel()
                    ->rule(static function (Get $get) {
                        return static function (string $attribute, $value, \Closure $fail) use ($get) {
                            $dialCode = get_dial_code($get('country_iso'));
                            $fullPhone = $dialCode.ltrim($value, '0');
                            $exists = User::where('phone_number', $fullPhone)
                                ->where('user_type_id', UserType::VENDOR)
                                ->whereNull('deleted_at')
                                ->exists();
                            if ($exists) {
                                $fail('This phone number is already registered.');
                            }
                        };
                    })
                    ->maxLength(20)
                    ->columnSpan(2),
            ]);
    }

    #[Override]
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $nameParts = explode(' ', $data['name'], 2);
        $data['first_name'] = $nameParts[0];
        $data['last_name'] = $nameParts[1] ?? '';

        unset($data['name']);

        // Combine fields and strip leading zero
        $dialCode = get_dial_code($data['country_iso']);
        $phoneInput = preg_replace('/[^0-9]/', '', $data['phone_number_input'] ?? '');
        $data['phone_number'] = $dialCode.ltrim($phoneInput, '0');
        unset($data['country_iso'], $data['phone_number_input']);

        $data['user_type_id'] = UserType::VENDOR;
        $data['user_status_id'] = UserStatus::PENDING;

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

    #[Override]
    protected function isRegisterRateLimited(string $email): bool
    {
        $phone = $this->data['phone_number_input'] ?? '';

        return parent::isRegisterRateLimited($phone);
    }
}
