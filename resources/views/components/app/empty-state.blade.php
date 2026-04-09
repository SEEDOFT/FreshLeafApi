@props([
    'title' => '',
    'reason' => '',
    'cta' => '',
])

<div class="empty-state">
    <div class="empty-icon" aria-hidden="true">+</div>
    <h3>{{ $title }}</h3>
    <p>{{ $reason }}</p>
    <button type="button" class="btn btn-primary">{{ $cta }}</button>
</div>
