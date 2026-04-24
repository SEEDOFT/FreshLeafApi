<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\UserType;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use Override;

class Login extends BaseLogin
{
    public function getHeading(): string
    {
        return 'Welcome back';
    }

    public function getSubHeading(): ?string
    {
        return 'Sign in to manage your FreshLeaf dashboard.';
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getPhoneNumberFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getPhoneNumberFormComponent(): TextInput
    {
        return TextInput::make('phone_number')
            ->label(__('custom.phone_number'))
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    #[Override]
    protected function getCredentialsFromFormData(array $data): array
    {
        $panelId = Filament::getCurrentOrDefaultPanel()->getId();

        // Dynamically add the required user_type_id to the login query
        // so we find the correct account for the panel (Admin vs Vendor)
        $userTypeId = match ($panelId) {
            'admin' => UserType::ADMIN,
            'vendor' => UserType::VENDOR,
            default => null,
        };

        return array_filter([
            'phone_number' => $data['phone_number'],
            'password' => $data['password'],
            'user_type_id' => $userTypeId,
        ]);
    }

    #[Override]
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.phone_number' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
