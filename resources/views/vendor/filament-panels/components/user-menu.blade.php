@props([
    'position' => null,
])

@php
    use Filament\Actions\Action;
    use Filament\Enums\UserMenuPosition;
    use Illuminate\Support\Arr;

    $user = filament()->auth()->user();

    if (method_exists($this, 'getUserMenuItems')) {
        $items = $this->getUserMenuItems();
    } else {
        $items = filament()->getUserMenuItems();
        foreach ($items as $action) {
            $action->defaultView($action::GROUPED_VIEW);
            if (method_exists($this, 'cacheAction')) {
                $this->cacheAction($action);
            }
        }
    }

    $itemsBeforeAndAfterThemeSwitcher = collect($items)
        ->groupBy(fn(Action $item): bool => $item->getSort() < 0, preserveKeys: true)
        ->all();
    $itemsBeforeThemeSwitcher = $itemsBeforeAndAfterThemeSwitcher[true] ?? collect();
    $itemsAfterThemeSwitcher = $itemsBeforeAndAfterThemeSwitcher[false] ?? collect();

    $itemsBeforeThemeSwitcher = $itemsBeforeThemeSwitcher->filter(fn ($item, $key) => $key !== 'profile');
    $itemsAfterThemeSwitcher = $itemsAfterThemeSwitcher->filter(fn ($item, $key) => $key !== 'profile');
    $hasProfileHeader = false;

    $position ??= filament()->getUserMenuPosition();

    $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
@endphp

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE) }}

<x-filament::dropdown
    :placement="($position === UserMenuPosition::Topbar) ? 'bottom-end' : 'top-end'"
    :teleport="$position === UserMenuPosition::Topbar"
    :attributes="
        \Filament\Support\prepare_inherited_attributes($attributes)
            ->class(['fi-user-menu'])
    "
>
    <x-slot name="trigger">
        @if ($position === UserMenuPosition::Topbar)
            <button
                aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}"
                type="button"
                class="fi-user-menu-trigger flex items-center gap-2 px-1.5 py-1.5 pr-3 rounded-full border border-gray-200/50 dark:border-white/10 bg-white/60 dark:bg-[#18181b]/80 backdrop-blur-md shadow-sm transition duration-200 hover:bg-white dark:hover:bg-white/5"
            >
                <x-filament-panels::avatar.user :user="$user" loading="lazy" class="w-7 h-7 rounded-full" />
                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">
                    {{ filament()->getUserName($user) }}
                </span>
                @svg('heroicon-m-chevron-down', 'w-4 h-4 text-gray-500 dark:text-gray-400')
            </button>
        @else
                <button
                    aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}"
                    type="button"
                    class="fi-user-menu-trigger"
                >
                    <x-filament-panels::avatar.user :user="$user" loading="lazy" />

                    <span
                        @if ($isSidebarCollapsibleOnDesktop)
                            x-show="$store.sidebar.isOpen"
                        @endif
                        class="fi-user-menu-trigger-text"
                    >
                        {{ filament()->getUserName($user) }}
                    </span>

                    {{
            \Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::ChevronUp, alias: \Filament\View\PanelsIconAlias::USER_MENU_TOGGLE_BUTTON, attributes: new \Illuminate\View\ComponentAttributeBag([
                'x-show' => $isSidebarCollapsibleOnDesktop ? '$store.sidebar.isOpen' : null,
            ]))
                    }}
                </button>
        @endif
    </x-slot>

    @if ($hasProfileHeader)
        @php
            $item = $itemsBeforeThemeSwitcher['profile'];
            $itemColor = $item->getColor();
            $itemIcon = $item->getIcon();

            unset($itemsBeforeThemeSwitcher['profile']);
        @endphp

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_BEFORE) }}

        <x-filament::dropdown.header :color="$itemColor" :icon="$itemIcon">
            {{ $item->getLabel() }}
        </x-filament::dropdown.header>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_AFTER) }}
    @endif

    @if ($itemsBeforeThemeSwitcher->isNotEmpty())
        <x-filament::dropdown.list>
            @foreach ($itemsBeforeThemeSwitcher as $key => $item)
                @if ($key === 'profile')
                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_BEFORE) }}

                    {{ $item }}

                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_AFTER) }}
                @else
                    {{ $item }}
                @endif
            @endforeach
        </x-filament::dropdown.list>
    @endif

    @if (filament()->hasDarkMode() && (!filament()->hasDarkModeForced()))
        <x-filament::dropdown.list>
            <x-filament-panels::theme-switcher />
        </x-filament::dropdown.list>
    @endif

    @if ($itemsAfterThemeSwitcher->isNotEmpty())
        <x-filament::dropdown.list>
            @foreach ($itemsAfterThemeSwitcher as $key => $item)
                @if ($key === 'profile')
                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_BEFORE) }}

                    {{ $item }}

                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_AFTER) }}
                @else
                    {{ $item }}
                @endif
            @endforeach
        </x-filament::dropdown.list>
    @endif
</x-filament::dropdown>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}
