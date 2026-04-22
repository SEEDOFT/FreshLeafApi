<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
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

        return $user;
    }
}
