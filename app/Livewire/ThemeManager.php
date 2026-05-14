<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ThemeManager extends Component
{
    #[On('updateTheme')]
    public function updateTheme(string $theme): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if (
            ! $user->whereIn('user_type_id', [UserType::ADMIN_ID, UserType::VENDOR_ID]) ||
            ! $user->where('user_status_id', UserStatus::ACTIVE_ID)
        ) {
            return;
        }

        // Update Admin Profile if it exists
        if ($user->where('user_type_id', UserType::ADMIN_ID)->exists()) {
            $user->adminProfile->update(['theme' => $theme]);
        }

        if ($user->where('user_type_id', UserType::VENDOR_ID)->exists()) {
            $user->vendorProfile->update(['theme' => $theme]);
        }
    }

    public function render(): string
    {
        return '<div></div>';
    }
}
