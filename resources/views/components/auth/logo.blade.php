{{--
    ================================================================
    Component: <x-auth.logo>
    Usage:
    <x-auth.logo />                   ← desktop (default)
    <x-auth.logo variant="mobile" />  ← mobile (larger icon)
    Props:
    $variant  string  'desktop' | 'mobile'
    ================================================================
--}}

@props(['variant' => 'desktop'])

<div {{ $attributes->merge(['class' => 'fl-auth__logo']) }}>

    <div class="fl-auth__logo-icon {{ $variant === 'mobile' ? 'fl-auth__logo-icon--lg' : '' }}">
        {{-- <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
            class="{{ $variant === 'mobile' ? 'w-8 h-8' : 'w-7 h-7' }}" aria-hidden="true">
            <path d="M24 6C18 6 10 10 8 18C6 26 10 34 16 38C20 40 24 40 28 38" stroke="#6ee7b7" stroke-width="2"
                stroke-linecap="round" />
            <path d="M24 6C28 8 36 14 38 22C40 30 36 38 30 41" stroke="#34d399" stroke-width="2"
                stroke-linecap="round" />
            <circle cx="24" cy="24" r="3" fill="#6ee7b7" />
            <line x1="24" y1="24" x2="16" y2="32" stroke="#6ee7b7" stroke-width="1.5" stroke-linecap="round" />
            <line x1="24" y1="24" x2="32" y2="16" stroke="#34d399" stroke-width="1.5" stroke-linecap="round" />
            <circle cx="16" cy="32" r="1.5" fill="#6ee7b7" />
            <circle cx="32" cy="16" r="1.5" fill="#34d399" />
            @if($variant === 'desktop')
                <line x1="16" y1="32" x2="12" y2="36" stroke="#6ee7b7" stroke-width="1" stroke-linecap="round" />
                <line x1="16" y1="32" x2="20" y2="36" stroke="#6ee7b7" stroke-width="1" stroke-linecap="round" />
            @endif
        </svg> --}}
        <img src="{{ Storage::url('images/fresh_leaf.png') }}" width="60" height="60"/>
    </div>

    <div class="fl-auth__logo-text {{ $variant === 'mobile' ? 'fl-auth__logo-text--mobile' : '' }}">
        <span class="fl-auth__logo-name">FreshLeaf</span>
        <span class="fl-auth__logo-sub">
            {{ $variant === 'mobile' ? 'Organics Admin' : 'Organics' }}
        </span>
    </div>

</div>
