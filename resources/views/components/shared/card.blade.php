@props([
    'padding' => 'p-6', // p-4, p-6, p-8
    'shadow' => 'shadow-md', // shadow-sm, shadow-md, shadow-lg, shadow-xl
    'rounded' => 'rounded-lg', // rounded-md, rounded-lg, rounded-xl
    'hover' => true,
    'border' => true,
])

@php
$baseClasses = 'bg-white dark:bg-gray-800 transition-all duration-300';

if ($shadow) {
    $baseClasses .= ' ' . $shadow;
}

if ($rounded) {
    $baseClasses .= ' ' . $rounded;
}

if ($border) {
    $baseClasses .= ' border border-gray-200 dark:border-gray-700';
}

if ($hover) {
    $baseClasses .= ' hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600';
}

if ($padding) {
    $baseClasses .= ' ' . $padding;
}
@endphp

<div {{ $attributes->merge(['class' => $baseClasses]) }}>
    @if(isset($header))
        <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            {{ $header }}
        </div>
    @endif
    
    {{ $slot }}
    
    @if(isset($footer))
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            {{ $footer }}
        </div>
    @endif
</div>
