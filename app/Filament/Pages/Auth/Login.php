<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\UserType;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
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
            ])
            ->statePath('data');
    }

    protected function getPhoneNumberFormComponent(): Grid
    {
        return Grid::make(3)
            ->schema([
                Select::make('country_code')
                    ->label('Code')
                    ->options([
                        '+855' => '+855 (KH)',
                        '+66' => '+66 (TH)',
                        '+84' => '+84 (VN)',
                        '+1' => '+1 (US)',
                    ])
                    ->default('+855')
                    ->required()
                    ->searchable()
                    ->columnSpan(1),
                TextInput::make('phone_number_input')
                    ->label(__('custom.phone_number'))
                    ->placeholder('12 345 678')
                    ->required()
                    ->tel()
                    ->autofocus()
                    ->autocomplete()
                    ->columnSpan(2),
            ]);
    }

    #[Override]
    protected function getCredentialsFromFormData(array $data): array
    {
        $panelId = Filament::getCurrentOrDefaultPanel()->getId();

        $userTypeId = match ($panelId) {
            'admin' => UserType::ADMIN,
            'vendor' => UserType::VENDOR,
            default => null,
        };

        // Combine code and number, stripping leading 0 from input number
        $fullPhone = $data['country_code'].ltrim($data['phone_number_input'], '0');

        return array_filter([
            'phone_number' => $fullPhone,
            'password' => $data['password'],
            'user_type_id' => $userTypeId,
        ]);
    }

    #[Override]
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.phone_number_input' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
