@extends('layouts.admin')

@section('content')
    <x-app.section :title="$moduleTitle" :meta="$moduleMeta">
        <div class="filter-bar">
            <a class="btn btn-soft" href="{{ route('admin.web.vendors.pending') }}">{{ __('panels.admin.back_to_pending') }}</a>
        </div>

        @if (session('status'))
            <p class="auth-flash">{{ session('status') }}</p>
        @endif

        <div class="detail-grid">
            <div class="timeline">
                <article class="timeline-item">
                    <h3>{{ __('panels.form.fields.name') }}</h3>
                    <p>{{ $vendor->first_name }} {{ $vendor->last_name }}</p>
                </article>
                <article class="timeline-item">
                    <h3>{{ __('panels.form.fields.email') }}</h3>
                    <p>{{ $vendor->email }}</p>
                </article>
                <article class="timeline-item">
                    <h3>{{ __('panels.form.fields.phone') }}</h3>
                    <p>{{ $vendor->phone_number }}</p>
                </article>
                <article class="timeline-item">
                    <h3>{{ __('panels.form.fields.business_name') }}</h3>
                    <p>{{ $vendor->vendorProfile?->business_name }}</p>
                </article>
                <article class="timeline-item">
                    <h3>{{ __('panels.form.fields.city') }} / {{ __('panels.form.fields.province') }}</h3>
                    <p>{{ $vendor->vendorProfile?->city }} / {{ $vendor->vendorProfile?->province }}</p>
                </article>
                <article class="timeline-item">
                    <h3>{{ __('panels.form.fields.address') }}</h3>
                    <p>{{ $vendor->vendorProfile?->address }}</p>
                </article>
            </div>

            <div class="empty-state review-actions">
                <h3>{{ __('panels.admin.review_actions') }}</h3>
                <p>{{ __('panels.admin.review_actions_meta') }}</p>
                <form method="POST" action="{{ route('admin.web.vendors.pending.approve', $vendor) }}">
                    @csrf
                    <button class="btn btn-primary" type="submit">{{ __('panels.admin.approve') }}</button>
                </form>
                <form method="POST" action="{{ route('admin.web.vendors.pending.reject', $vendor) }}">
                    @csrf
                    <button class="btn btn-soft" type="submit">{{ __('panels.admin.reject') }}</button>
                </form>
            </div>
        </div>
    </x-app.section>
@endsection
