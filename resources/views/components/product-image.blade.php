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
    // Generate a temporary signed URL when an image path exists so that secure media remains protected.
    $imageUrl = $image ? \App\Support\Storage\SecureStorage::temporarySignedUrl($image) : null;
@endphp

@if ($imageUrl)
    <img
        src="{{ $imageUrl }}"
        {{-- Preserve the original relative path for tooling/tests that rely on the raw storage location. --}}
        @if ($image)
            data-original-src="{{ $image }}"
        @endif
        alt="{{ $alt }}"
        class="{{ $classes }} rounded-lg object-cover shadow-sm"
        loading="lazy"
    >
@else
    <div class="{{ $classes }} flex items-center justify-center rounded-lg bg-gray-100 text-sm text-gray-500">
        {{-- Use the generic admin translation so the component aligns with shared copy expectations. --}}
        {{ __('admin.no_image') }}
    </div>
@endif
