@php
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
            isRevealable: {{ $field->isRevealable() ? 'true' : 'false' }},
            isRevealed: false,
        }" class="flex items-center w-full rounded-lg border border-gray-300 bg-white
        shadow-sm overflow-hidden transition duration-75
        focus-within:ring-2 focus-within:ring-primary-600 focus-within:border-primary-600
        dark:bg-white/5 dark:border-white/10
        dark:focus-within:border-primary-500 dark:focus-within:ring-primary-500">

        {{-- Fixed Lock Icon --}}
        <div class="flex items-center justify-center px-3 bg-gray-50 border-r border-gray-300
                        dark:bg-white/5 dark:border-white/10 shrink-0 select-none self-stretch">
            <x-heroicon-m-lock-closed class="w-5 h-5 text-gray-400 dark:text-gray-500" />
        </div>

        {{-- Password input --}}
        <input :type="isRevealed ? 'text' : 'password'" placeholder="{{ $field->getPlaceholder() ?? '••••••••' }}"
            wire:model.defer="{{ $statePath }}" @disabled($field->isDisabled()) @required($field->isRequired())
            {{ $field->getExtraInputAttributeBag() }}
            class="flex-1 w-full px-3 py-2 text-sm
                       bg-white dark:bg-transparent
                       border-none outline-none ring-0
                       text-gray-900 dark:text-white
                       placeholder-gray-400 dark:placeholder-gray-500
                       disabled:cursor-not-allowed disabled:opacity-60">

        {{-- Reveal Toggle --}}
        <template x-if="isRevealable">
            <button type="button" x-on:click="isRevealed = !isRevealed"
                class="flex items-center justify-center px-3 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                <x-heroicon-m-eye x-show="!isRevealed" class="w-5 h-5" />
                <x-heroicon-m-eye-slash x-show="isRevealed" class="w-5 h-5" x-cloak />
            </button>
        </template>
    </div>
</x-dynamic-component>
