<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Concerns\CanBeDisabled;
use Override;

class PasswordInput extends Field
{
    use CanBeDisabled, HasExtraInputAttributes, HasPlaceholder;

    #[Override]
    protected string $view = 'filament.forms.components.password-input';

    protected bool $isRevealable = true;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Override]
    public static function make(?string $name = 'password'): static
    {
        return parent::make($name);
    }

    public function revealable(bool $condition = true): static
    {
        $this->isRevealable = $condition;

        return $this;
    }

    public function isRevealable(): bool
    {
        return $this->isRevealable;
    }
}
