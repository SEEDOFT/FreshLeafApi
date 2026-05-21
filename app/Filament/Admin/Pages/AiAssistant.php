<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

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
        return __('admin.ai.assistant');
    }

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.app_control');
    }

    #[Override]
    public function getHeading(): string
    {
        return __('admin.ai.assistant');
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
        return UserSessionSecurity::isAuthorizedAs(UserType::ADMIN_ID);
    }

    #[Override]
    protected string $view = 'filament.pages.shared.ai-assistant';
}
