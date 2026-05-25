<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Models\SupportMessage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Override;

class SupportChat extends Page
{
    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    #[Override]
    protected string $view = 'filament.pages.shared.support-chat';

    #[Override]
    protected static ?string $slug = 'support-chat';

    #[Override]
    protected string|Width|null $maxContentWidth = Width::Full;

    #[Override]
    public function getHeading(): string
    {
        return __('admin.support.title');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('admin.support.nav_label');
    }

    // #[Override]
    // public static function getNavigationGroup(): ?string
    // {
    //     return __('admin.navigation.app_control');
    // }

    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $unreadCount = SupportMessage::where('sender_type', SupportMessage::USER)
            ->where('is_read', false)
            ->count();

        return $unreadCount > 0 ? (string) $unreadCount : null;
    }
}
