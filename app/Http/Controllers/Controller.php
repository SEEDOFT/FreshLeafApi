<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class Controller
{
    use ApiResponse;

    protected ?User $authenticatedUser = null;

    /**
     * Get the authenticated user.
     */
    protected function user(): User
    {
        if ($this->authenticatedUser instanceof User) {
            return $this->authenticatedUser;
        }

        /** @var User|null $user */
        $user = \auth()->user();

        if (! $user) {
            throw new HttpResponseException(
                static::errorResponse('Unauthenticated', 401),
            );
        }

        $this->authenticatedUser = $user;

        return $this->authenticatedUser;
    }
}
