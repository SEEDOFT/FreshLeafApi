<x-filament-panels::page>
    <div class="freshleaf-settings-shell">
        <form wire:submit="save" class="space-y-4">
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit" :disabled="auth()->user()->vendorProfile?->is_verified">
                    Save Changes
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
