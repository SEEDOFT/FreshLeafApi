<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use App\Models\Message;
use App\Models\UserType;
use App\Services\Auth\UserSessionSecurity;
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
    public static function canAccess(): bool
    {
        return UserSessionSecurity::isAuthorizedAs(UserType::VENDOR_ID);
    }

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
        $vendorId = auth()->id();
        $unreadCount = Message::where('is_read', false)
            ->where('sender_id', '!=', $vendorId)
            ->whereHas('conversation.participants', function ($query) use ($vendorId) {
                $query->where('user_id', $vendorId);
            })
            ->count();

        return $unreadCount > 0 ? (string) $unreadCount : null;
    }
}
