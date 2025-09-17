@props([
    'type' => 'info', // info|success|warning|error
])
@php
    $classes =
        [
            'info' => 'bg-blue-50 text-blue-800 border-blue-200',
            'success' => 'bg-green-50 text-green-800 border-green-200',
            'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
            'error' => 'bg-red-50 text-red-800 border-red-200',
        ][$type] ?? 'bg-blue-50 text-blue-800 border-blue-200';
@endphp
<div role="alert" aria-live="polite" {{ $attributes->merge(['class' => "border rounded-md p-3 text-sm {$classes}"]) }}>
    {{ $slot }}
</div>
