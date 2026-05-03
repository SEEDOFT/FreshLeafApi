<x-filament-panels::page>
    {{ $this->form }}

    <x-filament-actions::actions
        :actions="$this->getFormActions()"
    />
</x-filament-panels::page>
