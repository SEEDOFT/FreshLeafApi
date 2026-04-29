@php
    use Filament\Widgets\StatsOverviewWidget\Stat;
@endphp

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
    @foreach ($this->getStats() as $stat)
        <div 
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/5 shadow-sm"
            style="
                border-radius: 1rem;
                padding: 1.5rem;
                transition: all 0.3s ease;
            "
        >
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                <div>
                    <p class="text-gray-500 dark:text-gray-400" style="margin: 0; font-size: 0.875rem; font-weight: 500;">
                        {{ $stat->getLabel() }}
                    </p>
                    <p class="text-gray-900 dark:text-white" style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;">
                        {{ $stat->getValue() }}
                    </p>
                </div>
                @if ($icon = $stat->getDescriptionIcon())
                        <div style="color: {{ match ($stat->getColor()) {
                        'success' => '#10b981',
                        'info' => '#3b82f6',
                        'warning' => '#f59e0b',
                        'danger' => '#ef4444',
                        default => '#6b7280',
                    } }}; font-size: 1.5rem;">
                            @svg($icon, 'w-6 h-6')
                        </div>
                @endif
            </div>
            @if ($description = $stat->getDescription())
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: {{ match ($stat->getColor()) {
                    'success' => '#10b981',
                    'info' => '#3b82f6',
                    'warning' => '#f59e0b',
                    'danger' => '#ef4444',
                    default => '#6b7280',
                } }};">
                    {{ $description }}
                </p>
            @endif
        </div>
    @endforeach
</div>
