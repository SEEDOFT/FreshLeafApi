@props([
    'tone' => 'neutral',
    'label' => '',
])

<span class="status-chip tone-{{ $tone }}">{{ $label }}</span>
