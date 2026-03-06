@props([
    'product',
    'showThumbnails' => true,
    'aspectRatio' => 'aspect-square',
    'mainImageSize' => 'lg',
    'thumbnailSize' => 'sm'
])

@php
    $images = $product->getGalleryImages();
    $mainImage = $images[0] ?? null;
    $responsiveAttrs = $product->getResponsiveImageAttributes($mainImageSize);
@endphp

<div class="product-image-gallery" x-data="{ 
    currentImage: 0,
    images: @js($images)
}">
    {{-- Main Image Display --}}
    <div class="main-image-container {{ $aspectRatio }} overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800 relative group">
        @if($mainImage)
            <img 
                x-bind:src="images[currentImage]?.{{ $mainImageSize }} || images[currentImage]?.md"
                x-bind:srcset="images[currentImage] ? `${images[currentImage].xs} 150w, ${images[currentImage].sm} 300w, ${images[currentImage].md} 500w, ${images[currentImage].lg} 800w, ${images[currentImage].xl} 1200w` : ''"
                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 800px"
                x-bind:alt="images[currentImage]?.alt || '{{ $product->name }}'"
                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
            />
            
            {{-- Image Navigation Arrows --}}
            @if(count($images) > 1)
                <button 
                    @click="currentImage = currentImage > 0 ? currentImage - 1 : images.length - 1"
                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                    aria-label="{{ __('translations.previous_image') }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <button 
                    @click="currentImage = currentImage < images.length - 1 ? currentImage + 1 : 0"
                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                    aria-label="{{ __('translations.next_image') }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @endif

            {{-- Image Counter --}}
            @if(count($images) > 1)
                <div class="absolute bottom-2 right-2 bg-black/60 text-white px-2 py-1 rounded text-sm">
                    <span x-text="currentImage + 1"></span>/<span x-text="images.length"></span>
                </div>
            @endif

        @else
            {{-- No Image Placeholder --}}
            <div class="w-full h-full bg-gray-200 dark:bg-gray-700 flex flex-col items-center justify-center">
                <svg class="w-16 h-16 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-gray-500 text-sm">{{ __('frontend.images.no_image') }}</span>
            </div>
        @endif
    </div>

    {{-- Thumbnail Navigation --}}
    @if($showThumbnails && count($images) > 1)
        <div class="mt-4 flex gap-2 overflow-x-auto pb-2">
            @foreach($images as $index => $image)
                <button 
                    @click="currentImage = {{ $index }}"
                    class="flex-shrink-0 w-16 h-16 rounded-md overflow-hidden border-2 transition-colors duration-200"
                    :class="currentImage === {{ $index }} ? 'border-blue-500' : 'border-gray-200 hover:border-gray-300'"
                >
                    <img 
                        src="{{ $image['xs'] }}"
                        alt="{{ $image['alt'] }}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                </button>
            @endforeach
        </div>
    @endif

</div>
