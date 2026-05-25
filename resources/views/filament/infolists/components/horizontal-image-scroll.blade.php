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
        <div
            class="flex gap-3 overflow-x-auto py-2 scrollbar-thin"
            style="scrollbar-width: thin;"
        >
            @foreach ($images as $image)
                @php
                    $url = url(\Illuminate\Support\Facades\Storage::disk('public')->url($image));
                @endphp
                <img
                    src="{{ $url }}"
                    alt="{{ __('shared.product.visuals') }}"
                    class="h-40 w-40 flex-shrink-0 cursor-pointer rounded-xl object-cover ring-1 ring-gray-950/5 transition duration-150 hover:ring-primary-500 hover:shadow-lg dark:ring-white/10 dark:hover:ring-primary-400"
                    x-on:click="$dispatch('lightbox', { src: '{{ $url }}' })"
                >
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('admin.resources.general.not_provided') }}
        </p>
    @endif
</div>
