@props([
    'class' => '',
    'placeholder' => null,
    'maxResults' => 10,
    'minQueryLength' => 2,
    'filters' => [],
])

@php
    $contextFilters = collect($filters)
        ->only(['category', 'category_id', 'brand', 'brand_id', 'q', 'search'])
        ->filter(static fn ($value) => is_scalar($value) && $value !== '' && $value !== null)
        ->map(static fn ($value) => is_string($value) ? trim($value) : $value)
        ->all();
@endphp

<div class="search-module {{ $class }}">
    @livewire('components.live-search', [
        'maxResults' => $maxResults,
        'minQueryLength' => $minQueryLength,
        'placeholder' => $placeholder,
        'filters' => $contextFilters,
    ])
</div>
