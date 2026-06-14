@props([
    'src' => null,
    'name' => null,
    'icon' => 'heroicon-o-person',
    'size' => 'md', // xs, sm, md, lg, xl
    'class' => '',
])

@php
    $sizeClasses = match ($size) {
        'xs' => 'h-6 w-6 text-[10px]',
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-12 w-12 text-base',
        'xl' => 'h-16 w-16 text-lg',
        default => 'h-10 w-10 text-sm',
    };

    $initials = '';
    if ($name) {
        $words = preg_split("/\s+/", $name);
        if (count($words) >= 2) {
            $initials = strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1));
        } else {
            $initials = strtoupper(mb_substr($name, 0, 2));
        }
    }

    $hasImage = $src && !str_contains($src, 'user.png');

    $bgClasses = match (true) {
        $hasImage => '',
        $initials != '' => 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400',
        default => 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400',
    };
@endphp

<div {{ $attributes->merge(['class' => "relative inline-flex items-center justify-center rounded-full overflow-hidden shrink-0 {$sizeClasses} {$bgClasses} {$class}"]) }}>
    @if($hasImage)
        <img src="{{ $src }}" alt="{{ $name ?? 'Avatar' }}" class="h-full w-full object-cover">
    @elseif($initials)
        <span class="font-bold uppercase tracking-wider">
            {{ $initials }}
        </span>
    @else
        <x-filament::icon :icon="$icon" class="h-1/2 w-1/2" />
    @endif
</div>
