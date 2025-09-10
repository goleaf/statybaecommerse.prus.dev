<div class="product-image-gallery">
    @if ($this->hasImages)
        {{-- Main Image Display --}}
        <div class="aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800 relative group">
            @if ($this->currentImage)
                <img
                     src="{{ $this->currentImage[$this->imageSize] ?? $this->currentImage['md'] }}"
                     srcset="{{ $this->currentImage['xs'] }} 150w, {{ $this->currentImage['sm'] }} 300w, {{ $this->currentImage['md'] }} 500w, {{ $this->currentImage['lg'] }} 800w, {{ $this->currentImage['xl'] }} 1200w"
                     sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 800px"
                     alt="{{ $this->currentImage['alt'] }}"
                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105 cursor-zoom-in"
                     loading="lazy"
                     wire:click="toggleLightbox" />

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

                {{-- WebP Format Badge --}}
                <div class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-medium">
                    WebP
                </div>

                {{-- Generated Image Badge --}}
                @if ($this->currentImage['generated'] ?? false)
                    <div class="absolute top-2 right-2 bg-blue-500 text-white px-2 py-1 rounded text-xs font-medium">
                        {{ __('translations.random_image') }}
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
                            class="flex-shrink-0 w-16 h-16 rounded-md overflow-hidden border-2 transition-colors duration-200 {{ $this->currentImageIndex === $index ? 'border-blue-500' : 'border-gray-200 hover:border-gray-300' }}">
                        <img
                             src="{{ $image['xs'] }}"
                             alt="{{ $image['alt'] }}"
                             class="w-full h-full object-cover"
                             loading="lazy" />
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Image Information --}}
        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">
                    {{ __('translations.images') }}: {{ count($this->images) }}
                </span>
                <span class="text-green-600 dark:text-green-400 font-medium">
                    {{ __('translations.webp_format') }}
                </span>
            </div>
        </div>

        {{-- Lightbox Modal --}}
        @if ($showLightbox && $this->currentImage)
            <div
                 class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 transition-opacity duration-200"
                 wire:click="toggleLightbox"
                 wire:keydown.escape="toggleLightbox"
                 wire:keydown.arrow-left="previousImage"
                 wire:keydown.arrow-right="nextImage"
                 tabindex="0"
                 role="dialog"
                 aria-modal="true"
                 x-data
                 x-init="$nextTick(() => $el.focus())">
                <div class="relative max-w-4xl max-h-full transform transition-transform duration-200 ease-out" wire:click.stop>
                    <img
                         src="{{ $this->currentImage['xl'] ?? $this->currentImage['original'] }}"
                         alt="{{ $this->currentImage['alt'] }}"
                         class="max-w-full max-h-full object-contain" />

                    {{-- Close Button --}}
                    <button
                            wire:click="toggleLightbox"
                            class="absolute top-4 right-4 bg-white/20 hover:bg-white/30 text-white p-2 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    {{-- Navigation in Lightbox --}}
                    @if (count($this->images) > 1)
                        <button
                                wire:click="previousImage"
                                class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/30 text-white p-3 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <button
                                wire:click="nextImage"
                                class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/30 text-white p-3 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    @else
        {{-- No Images State with placeholder --}}
        <div class="aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800 relative">
            <img
                 src="{{ asset('images/placeholder-product.png') }}"
                 alt="{{ __('translations.no_image') }}"
                 class="w-full h-full object-cover"
                 loading="lazy" />
        </div>
    @endif
</div>
