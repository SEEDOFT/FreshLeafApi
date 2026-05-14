{{--
    ================================================================
    Component: <x-auth.form-card>
    Usage:
    <x-auth.form-card>
        ... form content ...
    </x-auth.form-card>
    Notes:
    Renders the white/glass card with the emerald accent bar on top.
    Passes through any attributes (e.g. style="animation-delay:80ms").
    ================================================================
--}}

<div {{ $attributes->merge(['class' => 'fl-auth__card']) }}>

    {{-- Emerald gradient accent bar --}}
    <div class="fl-auth__card-accent" aria-hidden="true"></div>

    {{-- Slotted content (the form) --}}
    {{ $slot }}

</div>
