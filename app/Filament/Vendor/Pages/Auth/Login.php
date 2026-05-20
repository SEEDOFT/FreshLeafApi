<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages\Auth;

use App\Filament\Forms\Components\PasswordInput;
use App\Filament\Forms\Components\PhoneNumberInput;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Override;

class Login extends BaseLogin
{
    #[Override]
    protected string $view = 'filament.vendor.pages.auth.login';

    #[Override]
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.base';
    }

    #[Override]
    public function getHeading(): string|Htmlable
    {
        return __('shared.auth.login.title');
    }

    #[Override]
    public function getSubHeading(): string|Htmlable|null
    {
        return __('shared.auth.login.subheading');
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getPhoneNumberFormComponent(),
                PasswordInput::make('password')
                    ->label(new HtmlString('<strong>'.__('shared.auth.login.password').'</strong>'))
                    ->required()
                    ->revealable(),
                TextEntry::make('registration_link')->placeholder(__('admin.resources.general.not_provided'))
                    ->hiddenLabel()
                    ->state(new HtmlString(
                        '<div class="text-sm text-center py-2">'.
                        __('shared.auth.login.not_having_account').' '.
                        '<a class="text-emerald-600 font-bold hover:text-emerald-500" href="'.
                        route('filament.vendor.auth.register').'">'.
                        __('shared.auth.login.register_here').
                        '</a>'.'</div>'
                    ))
                    ->html(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getPhoneNumberFormComponent(): Grid
    {
        return Grid::make(5)
            ->schema([
                PhoneNumberInput::make('phone_number')
                    ->label(new HtmlString('<strong>'.__('shared.auth.login.phone').'</strong>'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    protected function getCredentialsFromFormData(array $data): array
    {
        $vendor = User::where('phone_number', $data['phone_number'])
            ->where('user_type_id', UserType::VENDOR)
            ->first();

        if ($vendor && $vendor->user_status_id === UserStatus::PENDING_ID) {
            throw ValidationException::withMessages([
                'data.phone_number_input' => __('shared.auth.login.pending'),
            ]);
        }

        return array_filter([
            'phone_number' => $data['phone_number'],
            'password' => $data['password'],
            'user_status_id' => UserStatus::ACTIVE_ID,
            'user_type_id' => UserType::VENDOR_ID,
        ]);
    }

    #[Override]
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.phone_number' => __('shared.auth.login.failed'),
        ]);
    }

    public function switchLanguage(string $locale): void
    {
        session()->put('locale', $locale);
        session()->save();
        $this->redirect(request()->header('Referer') ?? url()->current());
    }
}
