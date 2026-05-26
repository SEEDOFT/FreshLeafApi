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
    protected function authenticatedUser(Request $request, ?int $userType = null): User
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $query = User::where('id', $user->id)
            ->where('user_status_id', UserStatus::ACTIVE_ID)
            ->where('phone_number', $user->phone_number);

        if ($userType !== null) {
            $query->where('user_type_id', $userType);
        }

        /** @var User|null $authorizedUser */
        $authorizedUser = $query->first();

        if (! $authorizedUser) {
            throw new AuthenticationException('User inactive or unauthorized.');
        }

        return $authorizedUser;
    }
}
