<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $panelTitle }} - {{ $moduleTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="panel-app" data-theme="{{ $panelPreferences['theme'] ?? 'light' }}" data-locale="{{ $panelPreferences['locale'] ?? app()->getLocale() }}">
    <div class="app-shell">
        <x-app.sidebar :items="$navItems" :currentModule="$module" :panelTitle="$panelTitle" />

        <main class="shell-main">
            <x-app.topbar :panelTitle="$panelTitle" :moduleTitle="$moduleTitle" :statusPill="$statusPill" />
            <div class="content-wrap">
                @yield('content')
            </div>
        </main>
    </div>

    <div id="app-toast" class="toast @if(session('status')) is-visible @endif" role="status" aria-live="polite">{{ session('status') }}</div>
</body>
</html>
