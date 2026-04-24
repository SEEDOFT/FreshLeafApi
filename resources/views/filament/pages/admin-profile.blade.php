<x-filament-panels::page>
    <div class="freshleaf-settings-shell">
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
