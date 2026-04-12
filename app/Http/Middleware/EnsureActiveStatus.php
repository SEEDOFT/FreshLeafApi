<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\AdminStatus;
use App\Models\Vendor;
use App\Models\VendorStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveStatus
{
    public function handle(Request $request, Closure $next, ?string $actor = null): Response
    {
        $user = $request->user();
        $normalizedActor = $actor !== null ? mb_strtolower(trim($actor)) : null;

        $isActive = match (true) {
            $user instanceof Vendor => (int) $user->status_id === VendorStatus::ACTIVE
                && ($normalizedActor === null || $normalizedActor === 'vendor'),
            $user instanceof Admin => (int) $user->status_id === AdminStatus::ACTIVE
                && ($normalizedActor === null || $normalizedActor === 'admin'),
            default => false,
        };

        if (! $isActive) {
            abort(403);
        }

        return $next($request);
    }
}
