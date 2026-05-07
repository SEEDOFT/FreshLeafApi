<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Support\Facades\Auth;

class UserSessionSecurity
{
    /**
     * Get the authorized user for the current session, applying hardened security checks.
     *
     * Logic: SELECT id FROM users WHERE user_type_id = ? and user_status_id = 2 and phone_number = ?
     */
    public static function getAuthorizedUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        // Hardened check
        return User::query()
            ->where('id', $user->id)
            ->where('user_type_id', $user->user_type_id)
            ->where('user_status_id', UserStatus::ACTIVE)
            ->where('phone_number', $user->phone_number)
            ->first();
    }

    /**
     * Check if the current user matches a specific type and is active.
     */
    public static function isAuthorizedAs(int $userTypeId): bool
    {
        $user = static::getAuthorizedUser();

        return $user && $user->user_type_id === $userTypeId;
    }
}
