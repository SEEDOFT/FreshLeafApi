<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Override;

class PhoneNumberInput extends Field
{
    #[Override]
    protected string $view = 'filament.forms.components.phone-number-input';

    protected string $defaultIso = 'KH';

    protected string $dialCode = '+855';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $dialCode = $this->dialCode;

        $this->dehydrateStateUsing(
            static function (?string $state) use ($dialCode): ?string {
                if (blank($state)) {
                    return null;
                }

                $cleaned = preg_replace('/\s+/', '', $state);
                $cleaned = ltrim($cleaned, '0');

                return $dialCode.$cleaned;
            }
        );
    }

    #[Override]
    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    public function getDefaultIso(): string
    {
        return $this->defaultIso;
    }

    public function getDialCode(): string
    {
        return $this->dialCode;
    }

    public function getCountryName(): string
    {
        return country(strtolower($this->defaultIso))->getName();
    }

    public function getFlagUrl(): string
    {
        return 'https://flagcdn.com/24x18/'.strtolower($this->defaultIso).'.png';
    }
}
