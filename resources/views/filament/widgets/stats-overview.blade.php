<div class="mb-8">
    @if ($heading = $this->getHeading())
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">{{ $heading }}</h3>
    @endif
    
    @php
        $columns = $this->getColumns();
        $gridClass = match($columns) {
            1 => 'lg:grid-cols-1',
            2 => 'lg:grid-cols-2',
            3 => 'lg:grid-cols-3',
            4 => 'lg:grid-cols-4',
            default => 'lg:grid-cols-2',
        };
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-2 {{ $gridClass }} gap-4">
        @foreach ($this->getStats() as $stat)
            @php
                $color = $stat->getColor() ?? 'gray';
                $iconClass = match ($color) {
                    'success' => 'text-emerald-500 bg-emerald-500/10',
                    'info' => 'text-teal-500 bg-teal-500/10',
                    'warning' => 'text-amber-500 bg-amber-500/10',
                    'danger' => 'text-rose-500 bg-rose-500/10',
                    'primary' => 'text-purple-400 bg-purple-400/10',
                    default => 'text-gray-400 bg-gray-400/10',
                };
                if ($color === 'info' && str_contains($stat->getLabel(), 'Users')) {
                    $iconClass = 'text-blue-500 bg-blue-500/10';
                }
                $isBadge = $stat->getExtraAttributes()['is_badge'] ?? false;
            @endphp
            <div class="bg-[#1c1c1e] dark:bg-[#18181b] border border-[#2a2a2d] dark:border-[#27272a] rounded-2xl p-5 flex justify-between items-start transition-all duration-300 ease-in-out shadow-sm hover:shadow-md">
                <div>
                    <p class="text-sm font-medium text-gray-400 dark:text-[#a1a1aa] m-0">
                        {{ $stat->getLabel() }}
                    </p>
                    <p class="text-3xl font-bold text-white dark:text-white mt-1 mb-0 truncate">
                        {{ $stat->getValue() }}
                    </p>
                    @if ($description = $stat->getDescription())
                        @if ($isBadge)
                            <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium text-amber-500 bg-amber-500/10">
                                @svg('heroicon-m-exclamation-triangle', 'w-4 h-4')
                                {{ $description }}
                            </div>
                        @else
                            <p class="text-sm text-gray-500 mt-2 mb-0 flex items-center gap-1">
                                @if($color === 'success' && str_contains($description, '0 pending'))
                                    <span class="text-emerald-500">{{ $description }}</span>
                                @elseif(str_contains($description, '1 USD'))
                                    <span class="text-emerald-500 inline-flex items-center gap-1">@svg('heroicon-m-arrow-trending-up', 'w-4 h-4') {{ $description }}</span>
                                @elseif(str_contains($description, '1 KHR'))
                                    <span class="text-rose-500 inline-flex items-center gap-1">@svg('heroicon-m-arrow-trending-down', 'w-4 h-4') {{ $description }}</span>
                                @else
                                    {{ $description }}
                                @endif
                            </p>
                        @endif
                    @endif
                </div>
                @if ($icon = $stat->getDescriptionIcon())
                    <div class="p-3 rounded-xl {{ $iconClass }}">
                        @svg($icon, 'w-6 h-6')
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
