@props([
    'image' => null,
    'alt' => '',
    'size' => 'md',
])

@php
    $dimensionClasses = [
        'sm' => 'h-16 w-16',
        'md' => 'h-24 w-24',
        'lg' => 'h-32 w-32',
    ];

    $classes = $dimensionClasses[$size] ?? $dimensionClasses['md'];
    $imageUrl = $image ? \App\Support\Storage\SecureStorage::temporarySignedUrl($image) : null;
@endphp

@if ($imageUrl)
    <img
        src="{{ $imageUrl }}"
        alt="{{ $alt }}"
        class="{{ $classes }} rounded-lg object-cover shadow-sm"
        loading="lazy"
    >
@else
    <div class="{{ $classes }} flex items-center justify-center rounded-lg bg-gray-100 text-sm text-gray-500">
        {{ __('admin.wishlist_items.no_image') }}
    </div>
@endif
