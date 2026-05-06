<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserType;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUserType
{
    use ApiResponse;

    /**
     * Handle Middleware
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || ! $user->isActive() || ! $user->isType(UserType::USER)) {
            return static::unauthorized('Unauthenticated.');
        }

        return $next($request);
    }
}
