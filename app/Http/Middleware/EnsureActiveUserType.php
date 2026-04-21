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
    public function handle(Request $request, Closure $next, string $type): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return static::unauthorized('Unauthenticated.');
        }

        if (! $user->isActive()) {
            return static::unauthorized('Unauthenticated.');
        }

        if (! $user->isType(self::resolveType($type))) {
            return static::unauthorized('Unauthenticated.');
        }

        return $next($request);
    }

    /**
     * Resolve Autheticated User Type
     */
    private static function resolveType(string $type): int
    {
        /** @var lowercase-string $normalized */
        $normalized = \mb_strtolower(\trim($type));

        return match ($normalized) {
            'vendor' => UserType::VENDOR,
            'admin' => UserType::ADMIN,
            'user', => UserType::USER,
            default => 0
        };
    }
}
