<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserType;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the authenticated user can view the target profile.
     */
    public function view(
        User $authenticatedUser,
        User $targetUser,
        ?int $expectedType = null,
    ): Response {
        return $this->ownsProfile($authenticatedUser, $targetUser, $expectedType);
    }

    /**
     * Determine whether the authenticated user can update the target profile.
     */
    public function update(
        User $authenticatedUser,
        User $targetUser,
        ?int $expectedType = null,
    ): Response {
        return $this->ownsProfile($authenticatedUser, $targetUser, $expectedType);
    }

    /**
     * Determine whether the authenticated user can delete the target profile.
     */
    public function delete(
        User $authenticatedUser,
        User $targetUser,
        ?int $expectedType = null,
    ): Response {
        return $this->ownsProfile($authenticatedUser, $targetUser, $expectedType);
    }

    private function ownsProfile(
        User $authenticatedUser,
        User $targetUser,
        ?int $expectedType,
    ): Response {
        // If expectedType is not provided (e.g. from Filament), infer it from the current panel
        if ($expectedType === null) {
            $panel = Filament::getCurrentPanel();

            if (! $panel) {
                return Response::deny();
            }

            $expectedType = match ($panel->getId()) {
                'admin' => UserType::ADMIN,
                'vendor' => UserType::VENDOR,
                default => null,
            };
        }

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

    private function hasProfileForType(User $targetUser, ?int $expectedType): bool
    {
        return match ($expectedType) {
            UserType::USER => $targetUser->userProfile()->exists(),
            UserType::VENDOR => $targetUser->vendorProfile()->exists(),
            UserType::ADMIN => $targetUser->adminProfile()->exists(),
            default => false,
        };
    }
}
