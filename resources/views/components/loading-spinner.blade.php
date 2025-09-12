@props([
    'size' => 'md',
    'color' => 'blue',
    'text' => null,
])

@php
    $sizes = [
        'sm' => 'w-4 h-4',
        'md' => 'w-8 h-8',
        'lg' => 'w-12 h-12',
        'xl' => 'w-16 h-16',
    ];

    $colors = [
        'blue' => 'border-blue-200 border-t-blue-600',
        'white' => 'border-white/20 border-t-white',
        'gray' => 'border-gray-200 border-t-gray-600',
        'green' => 'border-green-200 border-t-green-600',
        'red' => 'border-red-200 border-t-red-600',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $colorClass = $colors[$color] ?? $colors['blue'];
@endphp

<div class="flex flex-col items-center justify-center {{ $attributes->get('class') }}">
    <div class="border-4 {{ $colorClass }} rounded-full animate-spin {{ $sizeClass }}"></div>
    @if ($text)
        <p class="mt-3 text-sm text-gray-600 font-medium">{{ $text }}</p>
    @endif
</div>

