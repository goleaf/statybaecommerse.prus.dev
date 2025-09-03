@props([
    'variant' => 'primary', // primary, secondary, success, warning, danger, info, gray
    'size' => 'md', // sm, md, lg
    'rounded' => 'rounded-full', // rounded-md, rounded-lg, rounded-full
])

@php
$baseClasses = 'inline-flex items-center font-medium';

$variantClasses = match($variant) {
    'primary' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    'secondary' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    'success' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    'danger' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    'info' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    'gray' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
    default => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
};

$sizeClasses = match($size) {
    'sm' => 'px-2 py-1 text-xs',
    'md' => 'px-3 py-1 text-sm',
    'lg' => 'px-4 py-2 text-base',
    default => 'px-3 py-1 text-sm',
};

$classes = $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses . ' ' . $rounded;
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
