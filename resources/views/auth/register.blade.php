<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('panels.auth.register') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="panel-auth" data-theme="{{ $panelPreferences['theme'] ?? 'light' }}" data-locale="{{ $panelPreferences['locale'] ?? app()->getLocale() }}">
    <main class="auth-stage register-mode">
        <section class="auth-hero">
            <span class="auth-kicker">{{ __('panels.auth.kicker') }}</span>
            <h1>{{ __('panels.auth.register_title') }}</h1>
            <p>{{ __('panels.auth.register_subtitle') }}</p>

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
            <h2>{{ __('panels.auth.register') }}</h2>
            <p>{{ __('panels.auth.register_helper') }}</p>

            <div class="register-review-flow" aria-label="{{ __('panels.auth.review_steps_title') }}">
                <span class="review-step">{{ __('panels.auth.review_step_1') }}</span>
                <span class="review-step">{{ __('panels.auth.review_step_2') }}</span>
                <span class="review-step">{{ __('panels.auth.review_step_3') }}</span>
            </div>

            <form method="POST" action="{{ route('register.store') }}" class="auth-form auth-form-register">
                @csrf

                <div class="auth-block">
                    <p class="auth-block-title">{{ __('panels.auth.section_owner') }}</p>

                    <div class="auth-grid-two">
                        <label>
                            <span>{{ __('panels.form.fields.first_name') }} <em class="field-required">{{ __('panels.form.required') }}</em></span>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required>
                            @error('first_name') <small class="field-error">{{ $message }}</small> @enderror
                        </label>
                        <label>
                            <span>{{ __('panels.form.fields.last_name') }} <em class="field-required">{{ __('panels.form.required') }}</em></span>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required>
                            @error('last_name') <small class="field-error">{{ $message }}</small> @enderror
                        </label>
                    </div>

                    <div class="auth-grid-two">
                        <label>
                            <span>{{ __('panels.form.fields.email') }} <em class="field-required">{{ __('panels.form.required') }}</em></span>
                            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                            @error('email') <small class="field-error">{{ $message }}</small> @enderror
                        </label>
                        <label>
                            <span>{{ __('panels.form.fields.phone') }} <em class="field-required">{{ __('panels.form.required') }}</em></span>
                            <input type="text" name="phone_number" value="{{ old('phone_number') }}" autocomplete="tel" required>
                            @error('phone_number') <small class="field-error">{{ $message }}</small> @enderror
                        </label>
                    </div>
                </div>

                <div class="auth-block">
                    <p class="auth-block-title">{{ __('panels.auth.section_store') }}</p>

                    <div class="auth-grid-two">
                        <label>
                            <span>{{ __('panels.form.fields.business_name') }} <em class="field-required">{{ __('panels.form.required') }}</em></span>
                            <input type="text" name="business_name" value="{{ old('business_name') }}" required>
                            @error('business_name') <small class="field-error">{{ $message }}</small> @enderror
                        </label>
                        <label>
                            <span>{{ __('panels.form.fields.city') }} <em class="field-required">{{ __('panels.form.required') }}</em></span>
                            <input type="text" name="city" value="{{ old('city') }}" required>
                            @error('city') <small class="field-error">{{ $message }}</small> @enderror
                        </label>
                    </div>

                    <div class="auth-grid-two">
                        <label>
                            <span>{{ __('panels.form.fields.province') }} <em class="field-required">{{ __('panels.form.required') }}</em></span>
                            <input type="text" name="province" value="{{ old('province') }}" required>
                            @error('province') <small class="field-error">{{ $message }}</small> @enderror
                        </label>
                        <label>
                            <span>{{ __('panels.form.fields.address') }} <em class="field-required">{{ __('panels.form.required') }}</em></span>
                            <textarea name="address" rows="3" required>{{ old('address') }}</textarea>
                            @error('address') <small class="field-error">{{ $message }}</small> @enderror
                        </label>
                    </div>
                </div>

                <div class="auth-block">
                    <p class="auth-block-title">{{ __('panels.auth.section_security') }}</p>

                    <div class="auth-grid-two">
                        <label>
                            <span>{{ __('panels.form.fields.password') }} <em class="field-required">{{ __('panels.form.required') }}</em></span>
                            <input type="password" name="password" autocomplete="new-password" required>
                            @error('password') <small class="field-error">{{ $message }}</small> @enderror
                        </label>
                        <label>
                            <span>{{ __('panels.form.fields.password_confirmation') }} <em class="field-required">{{ __('panels.form.required') }}</em></span>
                            <input type="password" name="password_confirmation" autocomplete="new-password" required>
                        </label>
                    </div>

                    <p class="auth-form-note">{{ __('panels.auth.register_pending_note') }}</p>
                </div>

                <div class="auth-submit-row">
                    <button class="btn btn-primary" type="submit">{{ __('panels.auth.register') }}</button>
                    <p class="auth-support">
                        <a href="{{ route('login') }}">{{ __('panels.auth.back_to_login') }}</a>
                    </p>
                </div>
            </form>

            <p class="auth-support compact">{{ __('panels.auth.register_hint') }}</p>
        </section>
    </main>
</body>
</html>
