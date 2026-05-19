<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use App\Models\UserType;
use App\Services\Auth\UserSessionSecurity;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Override;

class AiAssistant extends Page
{
    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('shared.ai.assistant');
    }

    #[Override]
    public function getHeading(): string
    {
        return __('shared.ai.assistant');
    }

    #[Override]
    protected static ?string $slug = 'ai-assistant';

    #[Override]
    protected static ?int $navigationSort = 100;

    #[Override]
    protected string|Width|null $maxContentWidth = Width::Full;

    #[Override]
    public static function canAccess(): bool
    {
        return (bool) app_setting('enable_ai_assistant_vendor', true)
            && UserSessionSecurity::isAuthorizedAs(UserType::VENDOR_ID);
    }

    #[Override]
    protected string $view = 'filament.pages.ai-assistant';
}
