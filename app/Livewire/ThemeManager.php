<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\UserStatus;
use App\Models\UserType;
use App\Services\Auth\UserSessionSecurity;
use Livewire\Attributes\On;
use Livewire\Component;

class ThemeManager extends Component
{
    #[On('updateTheme')]
    public function updateTheme(string $theme): void
    {
        $user = UserSessionSecurity::getAuthorizedUser();

        if (! $user || ! $user->isActive()) {
            return;
        }

        if (
            ! $user->whereIn('user_type_id', [UserType::ADMIN_ID, UserType::VENDOR_ID]) ||
            ! $user->where('user_status_id', UserStatus::ACTIVE_ID)
        ) {
            return;
        }

        if ($user->where('user_type_id', UserType::ADMIN_ID)->exists()) {
            $user->adminProfile->update(['theme' => $theme]);
        }

        if ($user->where('user_type_id', UserType::VENDOR_ID)->exists()) {
            $user->vendorProfile->update(['theme' => $theme]);
        }
    }

    /**
     * Render
     */
    public function render(): string
    {
        return '<div></div>';
    }
}
