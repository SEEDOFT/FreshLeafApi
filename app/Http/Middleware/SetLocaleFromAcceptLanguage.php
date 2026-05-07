<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserType;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromAcceptLanguage
{
    use ApiResponse;

    /** @var list<string> */
    private const array SUPPORTED_LOCALES = ['km', 'en'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        if ($locale !== null) {
            \app()->setLocale((string) $locale);
        } else {
            $defaultLocale = config('app.locale');
            \app()->setLocale(\is_string($defaultLocale) ? $defaultLocale : 'en');
        }

        return $next($request);
    }

    /**
     * Resolve the locale from the request.
     *
     * @return string|null The resolved locale or null if not found
     */
    private function resolveLocale(Request $request): ?string
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return $this->detectFromHeader($request);
        }

        // 1. Resolve from specific profile based on user type
        $preferredLocale = match ($user->user_type_id) {
            UserType::ADMIN => $user->adminProfile?->locale,
            UserType::VENDOR => $user->vendorProfile?->locale,
            UserType::USER => $user->userProfile?->locale,
            default => null,
        };

        if ($preferredLocale !== null && \in_array($preferredLocale, self::SUPPORTED_LOCALES, true)) {
            return $preferredLocale;
        }

        return $this->detectFromHeader($request);
    }

    /**
     * Detect the locale from the Accept-Language header.
     *
     * @return string|null The detected locale or null if not found
     */
    private function detectFromHeader(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if ($acceptLanguage === null || $acceptLanguage === '') {
            return null;
        }

        $locale = \mb_strtolower(\mb_substr($acceptLanguage, 0, 2));

        if (\in_array($locale, self::SUPPORTED_LOCALES, true)) {
            return $locale;
        }

        $parts = \explode(',', $acceptLanguage);

        foreach ($parts as $part) {
            $code = \trim(\mb_strtolower(\mb_substr($part, 0, 2)));

            if (\in_array($code, self::SUPPORTED_LOCALES, true)) {
                return $code;
            }
        }

        return null;
    }
}
