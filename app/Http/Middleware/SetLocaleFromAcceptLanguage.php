<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
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
            \app()->setLocale($locale);
        } else {
            \app()->setLocale(app_setting('default_locale', config('app.locale')));
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

        // 1. Check User direct locale field (Admin/Vendor/Consumer)
        $preferredLocale = $user->locale;

        if (\in_array($preferredLocale, self::SUPPORTED_LOCALES, true)) {
            return $preferredLocale;
        }

        // 2. Check UserProfile (Consumer specific)
        $profileLocale = $user->userProfile?->preferred_language;

        if (
            \is_string($profileLocale) &&
            \in_array($profileLocale, self::SUPPORTED_LOCALES, true)
        ) {
            return $profileLocale;
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
