<div class="flex items-center gap-3 fi-custom-header-actions">
    @if (filament()->auth()->check())
        @if (filament()->hasDatabaseNotifications())
            @livewire(filament()->getDatabaseNotificationsLivewireComponent(), [
                'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
            ])
        @endif

        @if (filament()->hasUserMenu())
            <x-filament-panels::user-menu />
        @endif
    @endif
</div>
