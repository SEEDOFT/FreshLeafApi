<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\UserStatus;
use App\Models\UserType;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Override;

class Register extends BaseRegister
{
    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getPhoneNumberFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function getPhoneNumberFormComponent(): TextInput
    {
        return TextInput::make('phone_number')
            ->label(__('custom.phone_number'))
            ->required()
            ->unique($this->getUserModel(), 'phone_number', modifyRuleUsing: function ($rule) {
                return $rule->where('user_type_id', UserType::VENDOR)
                    ->whereNull('deleted_at');
            })
            ->maxLength(20);
    }

    #[Override]
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $nameParts = explode(' ', $data['name'], 2);
        $data['first_name'] = $nameParts[0];
        $data['last_name'] = $nameParts[1] ?? '';

        // Remove 'name' since it's not in our DB
        unset($data['name']);

        $data['user_type_id'] = UserType::VENDOR;
        $data['user_status_id'] = UserStatus::PENDING;

        return $data;
    }

    #[Override]
    protected function isRegisterRateLimited(string $email): bool
    {
        // Redirect rate limiting to phone number instead of email
        $phone = $this->data['phone_number'] ?? '';

        return parent::isRegisterRateLimited($phone);
    }
}
