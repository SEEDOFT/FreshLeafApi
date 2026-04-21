<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the authenticated user can view the target profile.
     */
    public function view(
        User $authenticatedUser,
        User $targetUser,
        int $expectedType,
    ): Response {
        return $this->ownsProfile($authenticatedUser, $targetUser, $expectedType);
    }

    /**
     * Determine whether the authenticated user can update the target profile.
     */
    public function update(
        User $authenticatedUser,
        User $targetUser,
        int $expectedType,
    ): Response {
        return $this->ownsProfile($authenticatedUser, $targetUser, $expectedType);
    }

    /**
     * Determine whether the authenticated user can delete the target profile.
     */
    public function delete(
        User $authenticatedUser,
        User $targetUser,
        int $expectedType,
    ): Response {
        return $this->ownsProfile($authenticatedUser, $targetUser, $expectedType);
    }

    private function ownsProfile(
        User $authenticatedUser,
        User $targetUser,
        int $expectedType,
    ): Response {
        if ((int) $authenticatedUser->user_type_id !== $expectedType) {
            return Response::denyAsNotFound('Profile not found.');
        }

        if (! $this->hasProfileForType($targetUser, $expectedType)) {
            return Response::denyAsNotFound('Profile not found.');
        }

        if ((int) $authenticatedUser->id === (int) $targetUser->id) {
            return Response::allow();
        }

        return Response::denyAsNotFound('Profile not found.');
    }

    private function hasProfileForType(User $targetUser, int $expectedType): bool
    {
        return match ($expectedType) {
            UserType::USER => $targetUser->userProfile()->exists(),
            UserType::VENDOR => $targetUser->vendorProfile()->exists(),
            UserType::ADMIN => $targetUser->adminProfile()->exists(),
            default => false,
        };
    }
}
