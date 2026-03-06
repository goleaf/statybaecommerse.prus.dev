<div class="product-image-gallery">
    @if ($this->hasImages)
        @php
            $currentImage = $this->currentImage;
        @endphp

        {{-- Main Image Display --}}
        <div class="aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800 relative group flex items-center justify-center">
            @if ($currentImage)
                <img
                     src="{{ $currentImage[$this->imageSize] ?? $currentImage['md'] }}"
                     srcset="{{ $currentImage['xs'] }} 150w, {{ $currentImage['sm'] }} 300w, {{ $currentImage['md'] }} 500w, {{ $currentImage['lg'] }} 800w, {{ $currentImage['xl'] }} 1200w"
                     sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 800px"
                     alt="{{ $currentImage['alt'] }}"
                     class="h-auto w-auto max-h-full max-w-full object-contain"
                     loading="lazy" />

                {{-- Image Navigation Arrows --}}
                @if (count($this->images) > 1)
                    <button
                            wire:click="previousImage"
                            class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                            aria-label="{{ __('translations.previous_image') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>

                    <button
                            wire:click="nextImage"
                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                            aria-label="{{ __('translations.next_image') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>
                @endif

                {{-- Image Counter --}}
                @if (count($this->images) > 1)
                    <div class="absolute bottom-2 right-2 bg-black/60 text-white px-2 py-1 rounded text-sm">
                        {{ $this->currentImageIndex + 1 }}/{{ count($this->images) }}
                    </div>
                @endif

            @endif
        </div>

        {{-- Thumbnail Navigation --}}
        @if (count($this->images) > 1)
            <div class="mt-4 flex gap-2 overflow-x-auto pb-2">
                @foreach ($this->images as $index => $image)
                    <button
                            wire:click="selectImage({{ $index }})"
                            class="flex-shrink-0 flex w-16 h-16 items-center justify-center rounded-md overflow-hidden border-2 transition-colors duration-200 {{ $this->currentImageIndex === $index ? 'border-blue-500' : 'border-gray-200 hover:border-gray-300' }}">
                        <img
                             src="{{ $image['xs'] }}"
                             alt="{{ $image['alt'] }}"
                             class="h-auto w-auto max-h-full max-w-full object-contain"
                             loading="lazy" />
                    </button>
                @endforeach
            </div>
        @endif

    @else
        {{-- No Images State with placeholder --}}
        <div class="aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800 relative">
            <img
                 src="{{ product_placeholder_url('large') }}"
                 alt="{{ __('translations.no_image') }}"
                 class="w-full h-full object-cover"
                 loading="lazy" />
        </div>
    @endif
</div>
