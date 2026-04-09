<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('panels.auth.login') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="panel-auth" data-theme="{{ $panelPreferences['theme'] ?? 'light' }}" data-locale="{{ $panelPreferences['locale'] ?? app()->getLocale() }}">
    <main class="auth-stage">
        <section class="auth-hero">
            <span class="auth-kicker">{{ __('panels.auth.kicker') }}</span>
            <h1>{{ __('panels.auth.hero_title') }}</h1>
            <p>{{ __('panels.auth.hero_subtitle') }}</p>

            <div class="auth-badges">
                <span class="auth-badge">{{ __('panels.auth.feature_fresh') }}</span>
                <span class="auth-badge">{{ __('panels.auth.feature_traceable') }}</span>
                <span class="auth-badge">{{ __('panels.auth.feature_delivery') }}</span>
            </div>

            <div class="auth-visual" aria-hidden="true">
                <div class="leaf leaf-one"></div>
                <div class="leaf leaf-two"></div>
                <div class="leaf leaf-three"></div>
            </div>
        </section>

        <section class="auth-card modern">
            <h2>{{ __('panels.auth.login') }}</h2>
            <p>{{ __('panels.auth.subtitle') }}</p>
            @if (session('status'))
                <p class="auth-flash">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                @csrf

                <label>
                    <span>{{ __('panels.form.fields.email') }}</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    <span>{{ __('panels.form.fields.password') }}</span>
                    <input type="password" name="password" required autocomplete="current-password">
                </label>

                <label class="field-inline remember-inline">
                    <input type="checkbox" name="remember" value="1">
                    <span>{{ __('panels.auth.remember') }}</span>
                </label>

                <button class="btn btn-primary" type="submit">{{ __('panels.auth.login') }}</button>
            </form>

            <p class="auth-support">
                <a href="{{ route('register') }}">{{ __('panels.auth.register_cta') }}</a>
            </p>
        </section>
    </main>
</body>
</html>
