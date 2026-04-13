<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class Controller
{
    use ApiResponse;

    /**
     * Get the authenticated user.
     */
    protected function user(): User
    {
        /** @var User|null */
        $user = auth()->user();

        if (! $user) {
            throw new HttpResponseException(
                static::errorResponse('Unauthenticated', 401),
            );
        }

        if (! $user->isActive()) {
            throw new HttpResponseException(
                static::errorResponse('Your account is not active', 403),
            );
        }

        return $user;
    }
}
