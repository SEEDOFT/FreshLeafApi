@if (!app()->runningUnitTests())
    @vite(['resources/js/app.js'])
@endif
