@props([
    'title' => '',
    'meta' => '',
    'actions' => null,
])

<section class="section-card">
    <header class="section-head">
        <div>
            <h2>{{ $title }}</h2>
            <p>{{ $meta }}</p>
        </div>

        @if ($actions !== null)
            <div class="section-actions">{{ $actions }}</div>
        @endif
    </header>

    <div class="section-body">
        {{ $slot }}
    </div>
</section>
