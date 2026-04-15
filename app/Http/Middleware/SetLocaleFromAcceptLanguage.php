<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromAcceptLanguage
{
    /** @var list<string> */
    private const array SUPPORTED_LOCALES = ['km', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        if ($locale !== null) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    private function resolveLocale(Request $request): ?string
    {
        $user = $request->user();

        if ($user === null && Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
        }

        $preferredLocale = $user?->preference?->locale;

        if (\is_string($preferredLocale) && \in_array($preferredLocale, self::SUPPORTED_LOCALES, true)) {
            return $preferredLocale;
        }

        return $this->detectFromHeader($request);
    }

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
