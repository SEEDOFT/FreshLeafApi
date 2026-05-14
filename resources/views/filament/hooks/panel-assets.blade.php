@if (!app()->runningUnitTests())
    @vite(['resources/js/app.js'])
    @livewire('theme-manager')
@endif

@auth
    @php
        $theme = Auth::user()?->adminProfile->theme ?? Auth::user()?->vendorProfile->theme ?? 'system';
    @endphp

    <script>
        // Sync database theme to localStorage on load
        (function () {
            const theme = @js($theme);
            if (theme && theme !== 'system') {
                localStorage.setItem('filament-theme', theme);
                document.documentElement.classList.remove('light', 'dark');
                document.documentElement.classList.add(theme);
            }
        })();

        // Observe theme changes to sync back to database
        document.addEventListener('DOMContentLoaded', () => {
            let lastTheme = localStorage.getItem('filament-theme') || 'system';

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                        const savedTheme = localStorage.getItem('filament-theme') || 'system';

                        // We check both the class and localStorage to determine the 'system' vs explicit preference
                        if (savedTheme !== lastTheme) {
                            lastTheme = savedTheme;
                            Livewire.dispatch('updateTheme', { theme: savedTheme });
                        }
                    }
                });
            });

            observer.observe(document.documentElement, { attributes: true });
        });
    </script>
@endauth
