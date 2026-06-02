<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                {{ __('admin.commission_fee.update_button') }}
            </x-filament::button>
        </div>
    </form>

    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            {{ __('admin.commission_fee.update_history_label') }}
        </h3>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
