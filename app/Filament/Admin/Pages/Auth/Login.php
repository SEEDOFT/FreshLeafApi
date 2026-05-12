<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages\Auth;

use App\Filament\Forms\Components\PhoneNumberInput;
use App\Models\UserStatus;
use App\Models\UserType;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use Override;

class Login extends BaseLogin
{
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
                $this->getPasswordFormComponent(),
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
        return array_filter([
            'phone_number' => $data['phone_number'],
            'password' => $data['password'],
            'user_status_id' => UserStatus::ACTIVE_ID,
            'user_type_id' => UserType::ADMIN_ID,
        ]);
    }

    #[Override]
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.phone_number_input' => __('admin.auth.login.failed'),
        ]);
    }
}
