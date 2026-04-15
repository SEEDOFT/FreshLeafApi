<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class Controller
{
    use ApiResponse;

    protected User $user;

    public function __construct()
    {
        $this->user = $this->user();
    }

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

        return $user;
    }
}
