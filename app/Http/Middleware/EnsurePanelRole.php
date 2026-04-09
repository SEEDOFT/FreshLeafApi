<?php

namespace App\Http\Middleware;

use App\Models\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanelRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isActive()) {
            abort(403);
        }

        $normalizedRole = mb_strtolower(trim($role));

        $isAuthorized = match ($normalizedRole) {
            'vendor' => $user->isType(UserType::VENDOR),
            'admin' => $user->isType(UserType::ADMIN),
            'super_admin' => $user->isType(UserType::ADMIN) && (bool) $user->adminProfile?->super_admin,
            default => false,
        };

        if (! $isAuthorized) {
            abort(403);
        }

        return $next($request);
    }
}
