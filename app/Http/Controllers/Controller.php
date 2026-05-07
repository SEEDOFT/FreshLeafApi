<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserStatus;
use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

abstract class Controller
{
    use ApiResponse;

    /**
     * Resolve authenticated user from request.
     *
     * @throws AuthenticationException
     */
    protected function authenticatedUser(Request $request): User
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException('Unauthenticated.');
        }

        // Hardened security check
        $authorizedUser = User::query()
            ->where('id', $user->id)
            ->where('user_type_id', $user->user_type_id)
            ->where('user_status_id', UserStatus::ACTIVE)
            ->where('phone_number', $user->phone_number)
            ->first();

        if (! $authorizedUser) {
            throw new AuthenticationException('Account inactive or unauthorized.');
        }

        return $authorizedUser;
    }
}
