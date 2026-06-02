<div class="content-wrapper-redesign">
    @if(count($financialStats) > 0)
        <div>
            <div class="section-label">Financial overview</div>
            <div class="stat-grid">
                @foreach($financialStats as $stat)
                    <div class="stat-card">
                        <div class="stat-card-row">
                            <div>
                                <div class="stat-label">{{ $stat['label'] }}</div>
                                <div class="stat-value">{{ $stat['value'] }}</div>
                            </div>
                            <div class="stat-icon {{ $stat['iconClass'] ?? 'blue' }}">
                                <x-filament::icon icon="{{ $stat['icon'] }}" class="h-5 w-5" />
                            </div>
                        </div>
                        @if(!empty($stat['description']))
                            <div class="stat-sub">{!! $stat['description'] !!}</div>
                        @elseif(!empty($stat['badgeClass']))
                            <div class="stat-sub">
                                <span class="{{ $stat['badgeClass'] }}">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                        <line x1="12" y1="9" x2="12" y2="13"/>
                                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                                    </svg>
                                    Awaiting disbursement
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(count($platformStats) > 0)
        <div>
            <div class="section-label">Platform activity</div>
            <div class="stat-grid-3">
                @foreach($platformStats as $stat)
                    <div class="stat-card">
                        <div class="stat-card-row">
                            <div>
                                <div class="stat-label">{{ $stat['label'] }}</div>
                                <div class="stat-value sm">{{ $stat['value'] }}</div>
                            </div>
                            <div class="stat-icon {{ $stat['iconClass'] ?? 'blue' }}">
                                <x-filament::icon icon="{{ $stat['icon'] }}" class="h-5 w-5" />
                            </div>
                        </div>
                        <div class="stat-sub {{ $stat['subClass'] ?? '' }}">{!! $stat['description'] !!}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(count($exchangeRates) > 0)
        <div>
            <div class="section-label">Exchange rates</div>
            <div class="stat-grid">
                @foreach($exchangeRates as $rate)
                    <div class="exchange-card">
                        <div class="stat-icon {{ $rate['iconClass'] ?? 'amber' }}" style="flex-shrink:0">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 17h-11M7 5l-4 4 4 4M7 9h11M17 19l4-4-4-4"/>
                            </svg>
                        </div>
                        <div style="flex:1">
                            <div class="ex-from">{{ $rate['from'] }} → {{ $rate['to'] }}</div>
                            <div class="ex-val">{{ $rate['rate'] }}</div>
                        </div>
                        <div class="stat-sub {{ $rate['trendClass'] ?? '' }}">
                            @if(($rate['trendClass'] ?? '') === 'pos')
                                <svg class="h-3 w-3 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 15l-6-6-6 6"/>
                                </svg>
                            @else
                                <svg class="h-3 w-3 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            @endif
                            {{ $rate['trend'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(count($chartData) > 0 && !empty($chartData['bars']))
        <div>
            <div class="trend-card">
                <div class="trend-header">
                    <span class="trend-title">{{ $chartData['title'] }}</span>
                    <span class="trend-period">{{ $chartData['period'] ?? 'Last 30 days' }}</span>
                </div>
                <div class="chart-area">
                    @foreach($chartData['bars'] as $bar)
                        <div class="bar {{ $bar['highlight'] ? 'hi' : '' }}" style="height: {{ $bar['height'] }}px;"></div>
                    @endforeach
                </div>
                <div class="chart-labels">
                    @foreach($chartData['labels'] as $label)
                        <span>{{ $label }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
