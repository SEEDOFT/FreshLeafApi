<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Update Commission Fee
            </x-filament::button>
        </div>
    </form>

    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            Update History
        </h3>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
