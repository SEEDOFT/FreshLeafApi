<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
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
    protected function authenticatedUser(Request $request, ?int $userType = UserType::USER): User
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $authorizedUser = User::where('id', $user->id)
            ->where('user_status_id', UserStatus::ACTIVE)
            ->where('phone_number', $user->phone_number)
            ->first();

        if (! $authorizedUser) {
            throw new AuthenticationException('Account inactive or unauthorized.');
        }

        return $authorizedUser;
    }
}
