<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages\Auth;

use App\Constants\StorageDirectory;
use App\Filament\Admin\Resources\Vendors\VendorResource;
use App\Filament\Forms\Components\PasswordInput;
use App\Filament\Forms\Components\PhoneNumberInput;
use App\Models\PaymentMethodStatus;
use App\Models\PaymentMethodType;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Closure;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Session;
use Livewire\Component;
use Override;

use function is_array;
use function is_string;
use function reset;

class Register extends BaseRegister
{
    #[Session(key: 'vendor-register-data')]
    public ?array $data = [];

    #[Override]
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.base';
    }

    #[Override]
    protected string $view = 'filament.vendor.pages.auth.register';

    #[Override]
    public function getHeading(): string|Htmlable
    {
        return __('shared.auth.register.title');
    }

    #[Override]
    public function getSubHeading(): string|Htmlable|null
    {
        return new HtmlString(
            __('shared.auth.register.subheading').' '.
            __('shared.auth.register.already_have_account').' '.
            '<a class="text-primary-600 font-medium hover:text-primary-500"'.
            ' href="'.route('filament.vendor.auth.login').'">'.
            __('shared.auth.register.login_here').
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
                    $this->getAccountStep(),
                    $this->getBusinessStep(),
                    $this->getVerificationStep(),
                    $this->getFinancialStep(),
                ])
                    ->persistStepInQueryString('step')
                    ->submitAction($this->getSubmitAction()),
            ])
            ->statePath('data');
    }

    protected function getAccountStep(): Step
    {
        return Step::make(__('shared.auth.register.steps.account'))
            ->icon('heroicon-o-user')
            ->description(__('shared.auth.register.steps.account_desc'))
            ->schema([
                Grid::make(1)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label(__('shared.auth.register.first_name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->label(__('shared.auth.register.last_name'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        TextInput::make('email')
                            ->label(__('shared.auth.register.email'))
                            ->maxLength(255),
                        $this->getPhoneNumberFormComponent(),
                        PasswordInput::make('password')
                            ->label(__('shared.auth.login.password'))
                            ->required()
                            ->revealable(),
                        PasswordInput::make('password_confirmation')
                            ->label(__('shared.auth.register.password_confirm'))
                            ->required()
                            ->revealable(),
                    ]),
            ]);
    }

    protected function getBusinessStep(): Step
    {
        return Step::make(__('shared.auth.register.steps.business'))
            ->icon('heroicon-o-building-storefront')
            ->description(__('shared.auth.register.steps.business_desc'))
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('business_name')
                        ->label(__('shared.auth.register.business_name'))
                        ->required()
                        ->maxLength(255),
                    PhoneNumberInput::make('contact_phone')
                        ->label(__('shared.form.fields.contact_phone'))
                        ->required(),
                    TextInput::make('village')
                        ->label(__('shared.form.fields.village'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('commune')
                        ->label(__('shared.form.fields.commune'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('district')
                        ->label(__('shared.form.fields.district'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('province')
                        ->label(__('shared.form.fields.province'))
                        ->required()
                        ->maxLength(255),
                ]),
            ]);
    }

    protected function getVerificationStep(): Step
    {
        return Step::make(__('shared.auth.register.steps.verification'))
            ->icon('heroicon-o-shield-check')
            ->description(__('shared.auth.register.steps.verification_desc'))
            ->schema([
                Grid::make(2)->schema([
                    FileUpload::make('id_card_front')
                        ->label(__('shared.auth.register.id_front'))
                        ->image()
                        ->disk('local')
                        ->directory(StorageDirectory::VENDOR_VERIFICATION)
                        ->required(),
                    FileUpload::make('id_card_back')
                        ->label(__('shared.auth.register.id_back'))
                        ->image()
                        ->disk('local')
                        ->directory(StorageDirectory::VENDOR_VERIFICATION)
                        ->required(),
                    FileUpload::make('store_front_image')
                        ->label(__('shared.auth.register.store_photo'))
                        ->image()
                        ->disk('public')
                        ->directory(StorageDirectory::SHOPS)
                        ->required(),
                    FileUpload::make('organic_certificate_url')
                        ->label(__('shared.auth.register.organic_cert'))
                        ->disk('local')
                        ->required()
                        ->directory(StorageDirectory::VENDOR_VERIFICATION),
                ]),
            ]);
    }

    protected function getFinancialStep(): Step
    {
        return Step::make(__('shared.auth.register.steps.financials'))
            ->icon('heroicon-o-banknotes')
            ->description(__('shared.auth.register.steps.financials_desc'))
            ->schema([
                Grid::make(3)->schema([
                    Select::make('payment_method_type_id')
                        ->label(__('shared.auth.register.bank_name'))
                        ->options(fn () => PaymentMethodType::whereIn('id', [
                            PaymentMethodType::ABA_ID,
                            PaymentMethodType::ACLEDA_ID,
                        ])->get()->pluck('translated_name', 'id')
                        )
                        ->required()
                        ->disabled()
                        ->default(PaymentMethodType::ABA_ID)
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            if ($state) {
                                $type = PaymentMethodType::find((int) $state);
                                $set('bank_name', $type?->name_en);
                            }
                        }),
                    TextInput::make('account_name')
                        ->label(__('shared.auth.register.account_holder'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('account_number')
                        ->label(__('shared.auth.register.account_number'))
                        ->required()
                        ->maxLength(255),
                ]),
                Hidden::make('bank_name'),
                FileUpload::make('qr_code')
                    ->label(__('shared.auth.register.qr_code'))
                    ->image()
                    ->disk('public')
                    ->directory(StorageDirectory::SHOPS)
                    ->required(),
            ]);
    }

    protected function getSubmitAction(): HtmlString
    {
        return new HtmlString(
            Blade::render(
                '<x-filament::button type="submit" size="sm" wire:click="register">'
                .__('shared.auth.register.complete')
                .'</x-filament::button>'
            )
        );
    }

    protected function getPhoneNumberFormComponent(): Grid
    {
        return Grid::make(5)
            ->schema([
                PhoneNumberInput::make('phone_number')
                    ->label(__('shared.auth.login.phone'))
                    ->required()
                    ->columnSpanFull()
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (PhoneNumberInput $component, Component $livewire): void {
                        $livewire->validateOnly($component->getStatePath());
                    })
                    ->rule(static function (): Closure {
                        return static function (string $attribute, mixed $value, Closure $fail): void {
                            $cleaned = (string) preg_replace('/\s+/', '', (string) $value);
                            $formattedPhone = str_starts_with($cleaned, '+855')
                                ? $cleaned
                                : '+855'.ltrim($cleaned, '0');

                            $exists = User::where('phone_number', $formattedPhone)
                                ->where('user_type_id', UserType::VENDOR_ID)
                                ->whereIn('user_status_id', [
                                    UserStatus::ACTIVE_ID,
                                    UserStatus::PENDING_ID,
                                    UserStatus::INACTIVE_ID,
                                ])
                                ->whereNull('deleted_at')
                                ->exists();

                            if ($exists) {
                                $fail(__('shared.auth.register.phone_registered'));
                            }
                        };
                    }),
            ]);
    }

    #[Override]
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['user_type_id'] = UserType::VENDOR_ID;
        $data['user_status_id'] = UserStatus::PENDING_ID;

        $imageFields = [
            'id_card_front',
            'id_card_back',
            'store_front_image',
            'organic_certificate_url',
            'qr_code',
        ];

        foreach ($imageFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $value = reset($data[$field]);
                $data[$field] = is_string($value) && $value !== '' ? $value : null;
            }
        }

        return $data;
    }

    #[Override]
    protected function handleRegistration(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $vendorData = Arr::only($data, [
                'business_name',
                'contact_phone',
                'village',
                'commune',
                'district',
                'province',
                'id_card_front',
                'id_card_back',
                'store_front_image',
                'organic_certificate_url',
                'payment_method_type_id',
                'bank_name',
                'account_name',
                'account_number',
                'qr_code',
            ]);

            $userData = Arr::except($data, array_keys($vendorData));

            /** @var User $user */
            $user = $this->getUserModel()::create([
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'] ?? null,
                'phone_number' => $userData['phone_number'],
                'image' => StorageDirectory::USERS.'/'.User::DEFAULT_PROFILE,
                'password' => Hash::make($userData['password']),
                'user_type_id' => $userData['user_type_id'],
                'user_status_id' => $userData['user_status_id'],
            ]);

            $user->vendorProfile()->create([
                'business_name' => $vendorData['business_name'],
                'contact_phone' => $vendorData['contact_phone'],
                'village' => $vendorData['village'],
                'commune' => $vendorData['commune'],
                'district' => $vendorData['district'],
                'province' => $vendorData['province'],
                'id_card_front' => $vendorData['id_card_front'],
                'id_card_back' => $vendorData['id_card_back'],
                'store_front_image' => $vendorData['store_front_image'],
                'organic_certificate_url' => $vendorData['organic_certificate_url'],
            ]);

            $user->vendorFinancialDetails()->create([
                'payment_method_type_id' => $vendorData['payment_method_type_id'],
                'payment_method_status_id' => PaymentMethodStatus::ACTIVE_ID,
                'bank_name' => $vendorData['bank_name'],
                'account_name' => $vendorData['account_name'],
                'account_number' => $vendorData['account_number'],
                'qr_code' => $vendorData['qr_code'],
            ]);

            $user->ensureDefaultWallets();

            return $user;
        });
    }

    #[Override]
    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        if ($this->isRegisterRateLimited($this->data['email'] ?? '')) {
            return null;
        }

        $user = null;
        $this->wrapInDatabaseTransaction(function () use (&$user): void {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->callHook('afterRegister');
        });

        session()->forget('vendor-register-data');

        Notification::make()
            ->title(__('shared.auth.register.pending_title'))
            ->body(__('shared.auth.register.pending_message'))
            ->success()
            ->send();

        $admins = User::active()
            ->ofType(UserType::ADMIN_ID)
            ->get();

        $notification = Notification::make()
            ->title(__('shared.auth.register.notification_title'))
            ->body(__('shared.auth.register.notification_body'))
            ->icon('heroicon-o-user-plus')
            ->success();

        if ($user) {
            $notification->actions([
                Action::make('view')
                    ->label(__('shared.auth.register.view_vendor'))
                    ->url(VendorResource::getUrl('view', ['record' => $user], panel: 'admin'))
                    ->button(),
            ]);
        }

        $notification->sendToDatabase($admins)->broadcast($admins);

        $this->redirect(route('filament.vendor.auth.login'));

        return null;
    }

    #[Override]
    protected function isRegisterRateLimited(string $email): bool
    {
        $phone = $this->data['phone_number'] ?? '';

        return parent::isRegisterRateLimited($phone);
    }

    public function switchLanguage(string $locale): void
    {
        session()->put('locale', $locale);
        session()->save();
        $this->redirect(request()->header('Referer') ?? url()->current());
    }
}
