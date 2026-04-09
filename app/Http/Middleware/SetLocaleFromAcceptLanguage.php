<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromAcceptLanguage
{
    private const array SUPPORTED_LOCALES = ['km', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->detectLocale($request);

        if ($locale !== null) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    private function detectLocale(Request $request): ?string
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
