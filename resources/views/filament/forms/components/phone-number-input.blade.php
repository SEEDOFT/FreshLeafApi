@php
    $iso = $field->getDefaultIso();
    $dialCode = $field->getDialCode();
    $flagUrl = $field->getFlagUrl();
    $countryName = $field->getCountryName();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
            formatted: '',
            format(val) {
                const digits = val.replace(/\D/g, '').slice(0, 8);
                const parts = [];
                if (digits.length > 0) parts.push(digits.slice(0, 2));
                if (digits.length > 2) parts.push(digits.slice(2, 5));
                if (digits.length > 5) parts.push(digits.slice(5, 8));
                this.formatted = parts.join(' ');

                // Directly update Livewire state with raw digits
                $wire.set('{{ $statePath }}', digits);
            },
            init() {
                const existing = $wire.get('{{ $statePath }}');
                if (existing) this.format(existing);
            }
        }" class="flex items-center w-full rounded-lg border border-gray-300 bg-white
        shadow-sm overflow-hidden transition duration-75
        focus-within:ring-2 focus-within:ring-primary-600 focus-within:border-primary-600
        dark:bg-white/5 dark:border-white/10
        dark:focus-within:border-primary-500 dark:focus-within:ring-primary-500">

        {{-- Fixed country flag + dial code --}}
        <div class="flex items-center gap-x-2 px-3 bg-gray-50 border-r border-gray-300
                        dark:bg-white/5 dark:border-white/10 shrink-0 select-none self-stretch py-0">
            <img src="{{ $flagUrl }}" width="24" height="18" alt="{{ $countryName }}" class="rounded-sm shadow-sm">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200 whitespace-nowrap">
                {{ $dialCode }}
            </span>
        </div>

        {{-- Phone number input --}}
        <input type="text" inputmode="numeric" placeholder="012 234 567" autocomplete="tel" x-model="formatted"
            x-on:input="format($event.target.value)" @disabled($isDisabled()) @required($isRequired()) class="flex-1 w-full px-3 py-2 text-sm
                       bg-white dark:bg-transparent
                       border-none outline-none ring-0
                       text-gray-900 dark:text-white
                       placeholder-gray-400 dark:placeholder-gray-500
                       disabled:cursor-not-allowed disabled:opacity-60">
    </div>
</x-dynamic-component>
