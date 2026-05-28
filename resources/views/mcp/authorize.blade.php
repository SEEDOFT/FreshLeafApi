<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <title>Authorize Application - {{ config('app.name', 'MCP Server') }}</title>

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Authorize MCP" />
    <link rel="manifest" href="/site.webmanifest" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-background text-foreground">
<div class="min-h-screen flex items-center justify-center px-3 py-4 sm:p-6">
    <div class="w-full max-w-md">
        <!-- Card Container -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <!-- Header -->
            <div class="flex flex-col space-y-1.5 p-4 sm:p-6">
                <div class="mb-3 flex items-center justify-center sm:mb-4">
                    <!-- Shield Icon -->
                    <svg class="h-10 w-10 text-primary sm:h-12 sm:w-12" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>

                <h3 class="text-center text-xl font-semibold leading-tight tracking-tight sm:text-2xl">
                    {{ __('shared.mcp.authorize') }} {{ $client->name }}
                </h3>

                <p class="text-sm text-muted-foreground text-center">
                    {{ __('shared.mcp.authorize_description') }}
                </p>
            </div>

            <!-- Content -->
            <div class="space-y-4 px-4 pb-0 pt-0 sm:p-6 sm:pt-0">
                <!-- User Info -->
                <div class="rounded-lg border bg-muted/50 p-3 sm:p-4">
                    <p class="text-sm text-muted-foreground mb-2">{{ __('shared.mcp.logged_in_as') }}</p>
                    <p class="break-all text-sm font-medium sm:text-base">{{ $user->email }}</p>
                </div>

                <!-- Scopes / Permissions -->
                @if(count($scopes) > 0)
                    <div class="space-y-2">
                        <p class="text-sm font-medium">{{ __('shared.mcp.permissions') }}</p>

                        <ul class="space-y-2">
                            @foreach($scopes as $scope)
                                <li class="flex items-start gap-2">
                                    <div class="rounded-full bg-primary/10 p-1 mt-0.5">
                                        <div class="h-1.5 w-1.5 rounded-full bg-primary"></div>
                                    </div>
                                    <span class="break-words text-sm text-muted-foreground">
                                        {{ $scope->description }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Footer With Buttons -->
            <div class="flex flex-col items-stretch gap-3 p-4 pt-0 sm:flex-row sm:items-center sm:p-6 sm:pt-0">
                <!-- Deny Form -->
                <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="state" value="">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center whitespace-nowrap rounded-md border border-input bg-background px-4 py-2 text-sm font-medium transition-colors ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground">
                        <svg class="mr-2 h-4 w-4" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        {{ __('shared.mcp.cancel') }}
                    </button>
                </form>

                <!-- Approve Form -->
                <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="flex-1" id="authorizeForm">
                    @csrf
                    <input type="hidden" name="state" value="">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center whitespace-nowrap rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:pointer-events-none hover:bg-primary/90" id="authorizeButton">
                        <span id="authorizeText">{{ __('shared.mcp.authorize') }}</span>

                        <svg id="loadingSpinner" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('authorizeForm');
        const button = document.getElementById('authorizeButton');
        const authorizeText = document.getElementById('authorizeText');
        const loadingSpinner = document.getElementById('loadingSpinner');

        form.addEventListener('submit', function(e) {
            // Show loading state...
            button.disabled = true;
            authorizeText.textContent = '{{ __('shared.mcp.authorizing') }}';
            loadingSpinner.classList.remove('hidden');

            // After form submission, watch for redirect and close window...
            setTimeout(function() {
                const checkRedirect = setInterval(function() {
                    // If URL changed or we have OAuth params, redirect happened...
                    if (!window.location.href.includes('/oauth/authorize') ||
                        window.location.search.includes('code=') ||
                        window.location.search.includes('error=')) {
                        clearInterval(checkRedirect);
                        window.close();
                    }
                }, 100);

                // Fallback: Close after five seconds...
                setTimeout(function() {
                    clearInterval(checkRedirect);
                    window.close();
                }, 5000);
            }, 200);
        });

        // Handle cancel button...
        const cancelForm = document.querySelector('form[method="POST"]:has(input[name="_method"][value="DELETE"])');
        if (cancelForm) {
            cancelForm.addEventListener('submit', function(e) {
                setTimeout(function() {
                    window.close();
                }, 200);
            });
        }
    });
</script>
</body>
</html>
