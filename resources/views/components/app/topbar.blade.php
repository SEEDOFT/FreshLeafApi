@props([
    'panelTitle' => '',
    'moduleTitle' => '',
    'statusPill' => ['label' => '', 'tone' => 'neutral'],
])

<header class="topbar">
    <div class="topbar-group">
        <h1 class="topbar-title">{{ $moduleTitle }}</h1>
        <p class="topbar-meta">{{ $panelTitle }}</p>
    </div>

    <div class="topbar-actions">
        <x-app.status-chip :tone="$statusPill['tone']" :label="$statusPill['label']" />

        <form method="POST" action="{{ route('preferences.locale') }}" class="field-inline">
            @csrf
            <label class="field-inline">
                <span>{{ __('panels.preferences.locale') }}</span>
                <select name="locale">
                    @foreach (($panelPreferences['locales'] ?? ['km', 'en']) as $supportedLocale)
                        <option value="{{ $supportedLocale }}" @selected(($panelPreferences['locale'] ?? app()->getLocale()) === $supportedLocale)>
                            {{ strtoupper($supportedLocale) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn btn-soft">{{ __('panels.preferences.apply') }}</button>
        </form>

        <form method="POST" action="{{ route('preferences.theme') }}">
            @csrf
            <input type="hidden" name="theme" value="{{ ($panelPreferences['theme'] ?? 'light') === 'dark' ? 'light' : 'dark' }}">
            <button type="submit" class="btn btn-soft">{{ __('panels.preferences.theme_toggle') }}</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-soft">{{ __('panels.auth.logout') }}</button>
        </form>
    </div>
</header>
