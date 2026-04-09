@extends('layouts.admin')

@section('content')
    <x-app.section :title="$moduleTitle" :meta="$moduleMeta">
        <div class="summary-grid">
            @foreach ($summaryCards as $card)
                <article class="summary-card">
                    <p class="summary-label">{{ $card['label'] }}</p>
                    <p class="summary-value">{{ $card['value'] }}</p>
                    <x-app.status-chip :tone="$card['tone']" :label="__('panels.status.active')" />
                </article>
            @endforeach
        </div>
    </x-app.section>

    <x-app.section :title="__('panels.pattern.data.title')" :meta="__('panels.pattern.data.meta')">
        <div class="filter-bar">
            <input type="text" placeholder="{{ __('panels.filters.search_placeholder') }}">
            <button class="btn btn-soft" type="button">{{ __('panels.filters.filter') }}</button>
            @if ($module === 'vendors')
                <a class="btn btn-soft" href="{{ $vendorsPendingRoute }}">{{ __('panels.admin.review_pending_vendors') }}</a>
            @endif
            <button class="btn btn-primary" type="button">{{ __('panels.filters.export') }}</button>
        </div>

        <div class="table-wrap skeleton-block" data-skeleton>
            <table>
                <thead>
                    <tr>
                        @foreach ($tableColumns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tableRows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['owner'] }}</td>
                            <td>{{ $row['updated'] }}</td>
                            <td><x-app.status-chip :tone="$row['status']" :label="__('panels.status.active')" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-app.section>

    <x-app.section :title="__('panels.pattern.detail.title')" :meta="__('panels.pattern.detail.meta')">
        <div class="detail-grid">
            <div class="timeline">
                @foreach ($timeline as $item)
                    <article class="timeline-item">
                        <p class="timeline-time">{{ $item['time'] }}</p>
                        <h3>{{ $item['title'] }}</h3>
                        <p>{{ $item['detail'] }}</p>
                    </article>
                @endforeach
            </div>

            <x-app.empty-state :title="$emptyState['title']" :reason="$emptyState['reason']" :cta="$emptyState['cta']" />
        </div>
    </x-app.section>

    <x-app.section :title="__('panels.pattern.form.title')" :meta="__('panels.pattern.form.meta')">
        <form class="form-grid" novalidate>
            @foreach ($formFields as $field)
                <label>
                    <span>{{ $field['label'] }}</span>
                    <input type="{{ $field['type'] }}" placeholder="{{ $field['placeholder'] }}">
                    <small class="field-error">{{ __('panels.form.inline_error') }}</small>
                </label>
            @endforeach

            <footer class="form-footer">
                <button type="button" class="btn btn-soft">{{ __('panels.form.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('panels.form.save') }}</button>
            </footer>
        </form>
    </x-app.section>
@endsection
