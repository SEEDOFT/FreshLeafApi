<x-filament-panels::page>
    <div class="freshleaf-settings-shell">
        <div class="mb-6 flex flex-wrap items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            @if (auth()->user()->image)
                <img
                    src="{{ Storage::url('users/'.auth()->user()->image) }}"
                    alt="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }} profile avatar"
                    class="h-20 w-20 rounded-full object-cover border border-gray-200 dark:border-gray-700"
                />
            @else
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-lg font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                    {{ strtoupper(substr(auth()->user()->first_name ?? auth()->user()->last_name ?? '', 0, 1)) }}
                </div>
            @endif

            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    Current Profile
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ auth()->user()->email }}
                </p>
            </div>
        </div>

        <form wire:submit="save" class="space-y-4">
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit">
                    Save Changes
                </x-filament::button>
            </div>
        </form>
    </div>

    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        Keep your account details accurate so team members can identify the right owner for operational actions.
    </div>

    <div class="mt-4">
        <div class="text-xs text-gray-500 dark:text-gray-400">
            Profile updates are applied immediately.
        </div>
    </div>
</x-filament-panels::page>
