<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetPanelPreferences
{
    private const array SUPPORTED_LOCALES = ['km', 'en'];

    private const array SUPPORTED_THEMES = ['light', 'dark'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        $theme = $this->resolveTheme($request);

        app()->setLocale($locale);

        $request->session()->put('preferences.locale', $locale);
        $request->session()->put('preferences.theme', $theme);

        View::share('panelPreferences', [
            'locale' => $locale,
            'theme' => $theme,
            'locales' => self::SUPPORTED_LOCALES,
            'themes' => self::SUPPORTED_THEMES,
        ]);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $sessionLocale = $request->session()->get('preferences.locale');

        if (is_string($sessionLocale) && in_array($sessionLocale, self::SUPPORTED_LOCALES, true)) {
            return $sessionLocale;
        }

        $cookieLocale = $request->cookie('fl_locale');

        if (is_string($cookieLocale) && in_array($cookieLocale, self::SUPPORTED_LOCALES, true)) {
            return $cookieLocale;
        }

        $acceptLanguage = $request->header('Accept-Language', 'km');
        $detectedLocale = mb_strtolower(mb_substr($acceptLanguage, 0, 2));

        if (in_array($detectedLocale, self::SUPPORTED_LOCALES, true)) {
            return $detectedLocale;
        }

        return 'km';
    }

    private function resolveTheme(Request $request): string
    {
        $sessionTheme = $request->session()->get('preferences.theme');

        if (is_string($sessionTheme) && in_array($sessionTheme, self::SUPPORTED_THEMES, true)) {
            return $sessionTheme;
        }

        $cookieTheme = $request->cookie('fl_theme');

        if (is_string($cookieTheme) && in_array($cookieTheme, self::SUPPORTED_THEMES, true)) {
            return $cookieTheme;
        }

        return 'light';
    }
}
