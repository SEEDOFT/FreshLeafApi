<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Models\Message;
use App\Models\UserType;
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

    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $unreadCount = Message::where('is_read', false)
            ->whereHas('conversation', function ($query) {
                $query->where('type', 'support');
            })
            ->whereHas('sender', function ($query) {
                $query->where('user_type_id', '!=', UserType::ADMIN_ID);
            })
            ->count();

        return $unreadCount > 0 ? (string) $unreadCount : null;
    }
}
