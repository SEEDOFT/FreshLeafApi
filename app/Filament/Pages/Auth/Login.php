<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Override;

class Login extends BaseLogin
{
    #[Override]
    public function getHeading(): string|Htmlable
    {
        return 'Welcome back, Admin!';
    }

    #[Override]
    public function getSubHeading(): string|Htmlable|null
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
                    ->placeholder('012 345 678')
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
        Log::info('[AdminLogin] Form submitted', ['keys' => array_keys($data)]);

        $panelId = Filament::getCurrentOrDefaultPanel()->getId();
        Log::info('[AdminLogin] Panel: '.$panelId);

        $userTypeId = match ($panelId) {
            'admin' => UserType::ADMIN,
            'vendor' => UserType::VENDOR,
            default => null,
        };

        // Combine dial code and number
        $countryIso = $data['country_iso'] ?? 'KH';
        $phoneInput = $data['phone_number_input'] ?? '';
        $dialCode = get_dial_code($countryIso);
        $fullPhone = $dialCode.ltrim($phoneInput, '0');

        Log::info('[AdminLogin] Country: '.$countryIso.', Dial: '.$dialCode.', Phone: '.$phoneInput.', Full: '.$fullPhone);

        $credentials = [
            'phone_number' => $fullPhone,
            'password' => $data['password'],
            'user_type_id' => $userTypeId,
        ];

        // Check if user exists and is pending
        $user = User::where('phone_number', $fullPhone)
            ->where('user_type_id', $userTypeId)
            ->first();

        Log::info('[AdminLogin] User found: '.($user ? $user->first_name.' (ID:'.$user->id.')' : 'NONE'));

        if ($user && $user->user_status_id === UserStatus::PENDING) {
            throw ValidationException::withMessages([
                'data.phone_number_input' => 'Your account is pending approval. Please wait for an administrator to review your application.',
            ]);
        }

        Log::info('[AdminLogin] Returning credentials for: '.$fullPhone);

        return array_filter($credentials);
    }

    #[Override]
    protected function throwFailureValidationException(): never
    {
        Log::error('[AdminLogin] LOGIN FAILED - Invalid credentials');
        throw ValidationException::withMessages([
            'data.phone_number_input' => 'Invalid credentials or user not authorized for this panel.',
        ]);
    }
}
