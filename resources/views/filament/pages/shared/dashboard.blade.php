<div class="fl-page-wrap">
    <div class="fl-page-head">
        <h1 class="fl-page-heading">{{ $this->getHeading() }}</h1>
        <p class="fl-page-subheading">{{ $this->getSubheading() }}</p>
    </div>
    <div class="fl-page-main">
        @livewire('dashboard-overview')
        
        <div class="mt-8">
            @livewire(\App\Filament\Admin\Widgets\PendingVendorPayouts::class)
        </div>
    </div>
</div>