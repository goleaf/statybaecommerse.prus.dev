@props([
    'product'
])

@php
    $images = $product->getGalleryImages();
@endphp

<div class="product-detail-images">
    @if(count($images) > 0)
        {{-- Main Image Gallery --}}
        <x-product.image-gallery 
            :product="$product"
            :showThumbnails="true"
            aspectRatio="aspect-square"
            mainImageSize="xl"
            thumbnailSize="xs"
        />

        {{-- Image Information Panel --}}
        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                {{ __('frontend.images.image_gallery') }}
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">
                        {{ __('frontend.images.images') }}:
                    </span>
                    <span class="text-gray-600 dark:text-gray-400">
                        {{ count($images) }} {{ __('frontend.images.images') }}
                    </span>
                </div>
                
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">
                        {{ __('frontend.images.webp_format') }}:
                    </span>
                    <span class="text-green-600 dark:text-green-400 font-medium">
                        ✓ {{ __('frontend.images.image_optimization') }}
                    </span>
                </div>
            </div>

            {{-- Available Image Sizes --}}
            <div class="mt-4">
                <span class="font-medium text-gray-700 dark:text-gray-300 block mb-2">
                    {{ __('translations.image_dimensions') }}:
                </span>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">150×150 (XS)</span>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">300×300 (SM)</span>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">500×500 (MD)</span>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">800×800 (LG)</span>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">1200×1200 (XL)</span>
                </div>
            </div>

            {{-- Generated Images Info --}}
            @php
                $generatedImages = collect($images)->where('generated', true);
            @endphp
            @if($generatedImages->isNotEmpty())
                <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-md">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a9 9 0 117.072 0l-.548.547A3.374 3.374 0 0014.846 21H9.154a3.374 3.374 0 00-3.182-2.263l-.548-.547z"></path>
                        </svg>
                        <span class="text-green-700 dark:text-green-300 text-sm font-medium">
                            {{ $generatedImages->count() }} {{ __('translations.random_image') }}{{ $generatedImages->count() > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
            @endif
        </div>

        {{-- All Images Grid (for admin/detailed view) --}}
        <div class="mt-8">
            <h5 class="text-md font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('translations.all_images') }} ({{ count($images) }})
            </h5>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($images as $index => $image)
                    <div class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                        <img 
                            src="{{ $image['sm'] }}"
                            srcset="{{ $image['xs'] }} 150w, {{ $image['sm'] }} 300w, {{ $image['md'] }} 500w"
                            sizes="(max-width: 640px) 50vw, (max-width: 1024px) 25vw, 200px"
                            alt="{{ $image['alt'] }}"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                            loading="lazy"
                        />
                        
                        {{-- Image Number --}}
                        <div class="absolute top-2 left-2 bg-black/60 text-white px-2 py-1 rounded text-xs">
                            #{{ $index + 1 }}
                        </div>

                        {{-- Generated Badge --}}
                        @if($image['generated'])
                            <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded text-xs">
                                {{ __('translations.random_image') }}
                            </div>
                        @endif

                        {{-- WebP Badge --}}
                        <div class="absolute bottom-2 right-2 bg-blue-500 text-white px-1 py-0.5 rounded text-xs">
                            WebP
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- No Images State --}}
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                {{ __('translations.no_image') }}
            </h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">
                {{ __('translations.no_images_description') }}
            </p>
        </div>
    @endif
</div>
