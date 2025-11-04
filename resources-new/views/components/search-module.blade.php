@props([
    'class' => '',
    'placeholder' => null,
    'maxResults' => 10,
    'minQueryLength' => 2,
])

<div class="search-module {{ $class }}">
    @livewire('components.live-search', [
        'maxResults' => $maxResults,
        'minQueryLength' => $minQueryLength,
        'placeholder' => $placeholder
    ])
</div>
