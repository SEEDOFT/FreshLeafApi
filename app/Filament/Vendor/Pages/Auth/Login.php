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
        return __('admin.auth.login.title');
    }

    #[Override]
    public function getSubHeading(): string|Htmlable|null
    {
        return __('admin.auth.login.subheading');
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getPhoneNumberFormComponent(),
                PasswordInput::make('password')
                    ->label(__('admin.auth.login.password'))
                    ->required()
                    ->revealable(),
                TextEntry::make('registration_link')
                    ->hiddenLabel()
                    ->state(new HtmlString(
                        '<div class="text-sm text-center py-2">'.
                        __('admin.auth.login.not_having_account').' '.
                        '<a class="text-emerald-600 font-bold hover:text-emerald-500" href="'.
                        route('filament.vendor.auth.register').'">'.
                        __('admin.auth.login.register_here').
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
                    ->label(__('admin.auth.login.phone'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    protected function getCredentialsFromFormData(array $data): array
    {
        $userTypeId = UserType::VENDOR;
        $credentials = [
            'phone_number' => $data['phone_number'],
            'password' => $data['password'],
            'user_type_id' => $userTypeId,
        ];

        $user = User::where('phone_number', $data['phone_number'])
            ->where('user_type_id', $userTypeId)
            ->first();

        if ($user && $user->user_status_id === UserStatus::PENDING_ID) {
            throw ValidationException::withMessages([
                'data.phone_number_input' => __('admin.auth.login.pending'),
            ]);
        }

        return array_filter($credentials);
    }
}
