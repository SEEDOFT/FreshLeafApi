@props([
    'items' => [],
    'currentModule' => 'dashboard',
    'panelTitle' => '',
])

<aside class="sidebar">
    <div class="sidebar-brand">
        <strong>{{ $panelTitle }}</strong>
    </div>

    <nav class="sidebar-nav">
        @foreach ($items as $item)
            <a href="{{ $item['href'] }}" class="nav-link @if($currentModule === $item['module']) is-active @endif">
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</aside>
