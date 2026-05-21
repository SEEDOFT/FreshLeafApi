<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserType;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureActiveUserType
{
    use ApiResponse;

    /**
     * Handle Middleware
     */
    public function handle(Request $request, Closure $next): mixed
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user || ! $user->isActive()) {
            /** @var JsonResponse $response */
            $response = response()->json(['message' => 'Unauthenticated.'], 401);

            return $response;
        }

        return $next($request);
    }
}
