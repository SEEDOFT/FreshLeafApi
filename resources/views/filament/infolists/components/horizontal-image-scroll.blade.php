@php
    $images = \Illuminate\Support\Arr::wrap($getState());
@endphp

<div class="fi-in-entry">
    @if ($getLabel())
        <label class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                {{ $getLabel() }}
            </span>
        </label>
    @endif

    @if (count($images) > 0)
        <div class="fl-image-scroll">
            @foreach ($images as $image)
                @php
                    $url = url(\Illuminate\Support\Facades\Storage::disk('public')->url($image));
                @endphp
                <img src="{{ $url }}" alt="{{ __('shared.product.visuals') }}"
                    class="fl-image-scroll__img"
                    x-on:click="$dispatch('lightbox', { src: '{{ $url }}' })">
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('admin.resources.general.not_provided') }}
        </p>
    @endif
</div>