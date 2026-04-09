@extends('layouts.admin')

@section('content')
    <x-app.section :title="$moduleTitle" :meta="$moduleMeta">
        <div class="filter-bar">
            <a class="btn btn-soft" href="{{ route('admin.module', ['module' => 'vendors']) }}">{{ __('panels.admin.back_to_vendors') }}</a>
        </div>

        @if (session('status'))
            <p class="auth-flash">{{ session('status') }}</p>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('panels.table.columns.name') }}</th>
                        <th>{{ __('panels.form.fields.business_name') }}</th>
                        <th>{{ __('panels.table.columns.updated') }}</th>
                        <th>{{ __('panels.table.columns.status') }}</th>
                        <th>{{ __('panels.admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendors as $vendor)
                        <tr>
                            <td>{{ $vendor->first_name }} {{ $vendor->last_name }}</td>
                            <td>{{ $vendor->vendorProfile?->business_name ?? '-' }}</td>
                            <td>{{ optional($vendor->updated_at)->format('Y-m-d H:i') }}</td>
                            <td><x-app.status-chip tone="warning" :label="__('panels.status.pending')" /></td>
                            <td>
                                <a class="btn btn-soft" href="{{ route('admin.web.vendors.pending.show', $vendor) }}">{{ __('panels.admin.review') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">{{ __('panels.admin.no_pending_vendors') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $vendors->links() }}
        </div>
    </x-app.section>
@endsection
