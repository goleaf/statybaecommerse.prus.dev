{{-- Shared Untitled UI icon wrapper so sibling components can stay minimal while keeping view caching happy. --}}
@props(['paths' => [], 'd' => null])

@php
    $computedPaths = $paths;

    if ($d !== null) {
        $computedPaths = array_merge([$d], (array) $computedPaths);
    }

    if (! is_array($computedPaths)) {
        $computedPaths = [$computedPaths];
    }
@endphp

<svg {{ $attributes->merge(['xmlns' => 'http://www.w3.org/2000/svg', 'fill' => 'none', 'viewBox' => '0 0 24 24', 'stroke' => 'currentColor']) }}>
    @foreach ($computedPaths as $path)
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
    @endforeach
</svg>
