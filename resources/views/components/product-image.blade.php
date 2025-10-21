@props([
    'image' => null,
    'alt' => null,
])

@php
    $altText = filled($alt) ? $alt : __('Product image');
    $imagePath = null;

    if ($image instanceof \App\Models\ProductImage) {
        $imagePath = $image->path;
        $altText = filled($alt) ? $alt : ($image->alt_text ?? $altText);
    } elseif (is_array($image)) {
        $imagePath = \Illuminate\Support\Arr::get($image, 'path')
            ?? \Illuminate\Support\Arr::get($image, 'url')
            ?? \Illuminate\Support\Arr::get($image, 'src');
        $altText = filled($alt)
            ? $alt
            : (\Illuminate\Support\Arr::get($image, 'alt')
                ?? \Illuminate\Support\Arr::get($image, 'alt_text')
                ?? $altText);
    } elseif (is_string($image) && $image !== '') {
        $imagePath = $image;
    }

    $imageUrl = null;
    if (filled($imagePath)) {
        $imageUrl = \Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://', 'data:', '/'])
            ? $imagePath
            : asset(trim($imagePath, '/'));
    }
@endphp

<div {{ $attributes->class(['w-full max-w-sm']) }}>
    <div class="aspect-1 ring-1 ring-gray-100 overflow-hidden rounded-lg">
        @if ($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $altText }}"
                loading="lazy"
                class="size-full max-w-none object-cover object-center"
            />
        @else
            <div class="size-full max-w-none bg-gray-200 flex flex-col items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="m3 15 4.5-4.5a2 2 0 0 1 2.828 0L17 17m-2-2 1.5-1.5a2 2 0 0 1 2.828 0L21 15M8.25 8.25h.008v.008H8.25Z" />
                </svg>
                <span class="text-sm font-medium text-gray-500">{{ __('admin.no_image') }}</span>
            </div>
        @endif
    </div>
</div>
