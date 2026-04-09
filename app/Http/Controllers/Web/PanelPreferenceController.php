<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class PanelPreferenceController extends Controller
{
    public function updateLocale(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:km,en'],
        ]);

        $locale = $validated['locale'];

        $request->session()->put('preferences.locale', $locale);

        return redirect()->back()
            ->with('status', __('panels.preferences.locale_updated'))
            ->cookie(new Cookie('fl_locale', $locale, now()->addYear(), secure: false, httpOnly: false, sameSite: 'lax'));
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark'],
        ]);

        $theme = $validated['theme'];

        $request->session()->put('preferences.theme', $theme);

        return redirect()->back()
            ->with('status', __('panels.preferences.theme_updated'))
            ->cookie(new Cookie('fl_theme', $theme, now()->addYear(), secure: false, httpOnly: false, sameSite: 'lax'));
    }
}
