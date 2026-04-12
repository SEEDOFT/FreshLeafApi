<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\Vendor;
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
        $normalizedRole = mb_strtolower(trim($role));

        $isAuthorized = match ($normalizedRole) {
            'vendor' => $user instanceof Vendor,
            'admin' => $user instanceof Admin,
            'super_admin' => $user instanceof Admin && $user->super_admin,
            default => false,
        };

        if (! $isAuthorized) {
            abort(403);
        }

        return $next($request);
    }
}
