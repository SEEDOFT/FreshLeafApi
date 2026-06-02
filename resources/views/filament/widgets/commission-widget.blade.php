<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @foreach ($this->getStats() as $stat)
        @php
            $iconColorClass = match ($stat->getColor()) {
                'success' => 'text-emerald-500',
                'info' => 'text-blue-500',
                'warning' => 'text-amber-500',
                'danger' => 'text-red-500',
                default => 'text-gray-500',
            };
            $descColorClass = match ($stat->getColor()) {
                'success' => 'text-emerald-500',
                'info' => 'text-blue-500',
                'warning' => 'text-amber-500',
                'danger' => 'text-red-500',
                default => 'text-gray-500 dark:text-gray-400',
            };
        @endphp
        <div class="bg-white dark:bg-[#18181b] border border-gray-200 dark:border-[#27272a] rounded-2xl p-6 transition-all duration-300 ease-in-out shadow-sm hover:shadow-md">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-[#a1a1aa] m-0">
                        {{ $stat->getLabel() }}
                    </p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2 mb-0 truncate">
                        {{ $stat->getValue() }}
                    </p>
                </div>
                @if ($icon = $stat->getDescriptionIcon())
                    <div class="{{ $iconColorClass }}">
                        @svg($icon, 'w-8 h-8')
                    </div>
                @endif
            </div>
            @if ($description = $stat->getDescription())
                <p class="text-sm mt-2 mb-0 {{ $descColorClass }}">
                    {{ $description }}
                </p>
            @endif
        </div>
    @endforeach
</div>
