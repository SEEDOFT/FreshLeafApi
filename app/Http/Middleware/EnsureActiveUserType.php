<?php

namespace App\Http\Middleware;

use App\Models\UserType;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUserType
{
    use ApiResponse;

    public function handle(Request $request, Closure $next, string $type): Response
    {
        $user = $request->user();

        if (! $user) {
            return static::forbidden('Unauthenticated.');
        }

        if (! $user->isActive()) {
            return static::forbidden('Your account is not active.');
        }

        $requiredType = $this->resolveType($type);

        if ($requiredType === null) {
            return static::forbidden('Invalid user type constraint.');
        }

        if (! $user->isType($requiredType)) {
            return static::forbidden('You are not authorized for this resource.');
        }

        return $next($request);
    }

    private static function resolveType(string $type): ?int
    {
        $normalized = \mb_strtolower(\trim($type));

        return match ($normalized) {
            'consumer' => UserType::CONSUMER,
            'vendor' => UserType::VENDOR,
            'admin' => UserType::ADMIN,
            default => \is_numeric($normalized) ? (int) $normalized : null,
        };
    }
}
