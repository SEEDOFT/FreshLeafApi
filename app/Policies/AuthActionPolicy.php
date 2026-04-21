<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class AuthActionPolicy
{
    /**
     * Determine whether the authenticated user can logout.
     */
    public function logout(User $user): Response
    {
        return Response::allow();
    }

    /**
     * Determine whether the authenticated user can verify password.
     */
    public function verifyPassword(User $user): Response
    {
        return Response::allow();
    }

    /**
     * Determine whether the authenticated user can update password.
     */
    public function updatePassword(User $user): Response
    {
        return Response::allow();
    }
}
