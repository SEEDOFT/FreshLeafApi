<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Models\ConversationType;
use App\Models\Message;
use App\Models\UserType;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
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
            ->whereHas('conversation', static function (Builder $query): void {
                $query->where('conversation_type_id', ConversationType::SUPPORT_ID);
            })
            ->whereHas('sender', static function (Builder $query): void {
                $query->where('user_type_id', '!=', UserType::ADMIN_ID);
            })
            ->count();

        return $unreadCount > 0 ? (string) $unreadCount : null;
    }

    #[Override]
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }
}
