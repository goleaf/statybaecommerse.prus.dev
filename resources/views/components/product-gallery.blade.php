@props([
    'product' => null,
    'images' => null,
    'showThumbnails' => true,
    'showZoom' => true,
    'showFullscreen' => true,
    'autoplay' => false,
    'autoplayInterval' => 3000,
    'maxThumbnails' => 5,
])

@php
    $product = $product ?? new \App\Models\Product();
    $images = $images ?? $product->getMedia('images');

    // If no images, use placeholder
    if ($images->isEmpty()) {
        $images = collect([
            (object) ['url' => asset('images/placeholder-product.jpg'), 'alt' => $product->name ?? 'Product Image'],
        ]);
    }

    $totalImages = $images->count();
@endphp

<div class="product-gallery" x-data="productGallery()" x-init="init()">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Main Image Display --}}
        <div class="lg:col-span-3">
            <div class="relative bg-white border border-gray-200 rounded-2xl overflow-hidden">
                {{-- Main Image Container --}}
                <div class="relative aspect-w-1 aspect-h-1 bg-gray-100 overflow-hidden">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <img x-ref="mainImage"
                             :src="currentImage.url"
                             :alt="currentImage.alt"
                             class="max-w-full max-h-full object-contain transition-opacity duration-300"
                             @click="openFullscreen()"
                             @mouseenter="showZoom = true"
                             @mouseleave="showZoom = false"
                             @mousemove="handleMouseMove($event)">
                    </div>

                    {{-- Zoom Overlay --}}
                    <div x-show="showZoom && zoomEnabled"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute inset-0 bg-black/20 pointer-events-none"
                         style="display: none;">
                        <div class="absolute w-32 h-32 border-2 border-white rounded-lg pointer-events-none"
                             :style="`left: ${zoomX - 64}px; top: ${zoomY - 64}px;`"></div>
                    </div>

                    {{-- Navigation Arrows --}}
                    @if ($totalImages > 1)
                        <button @click="previousImage()"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700 hover:bg-white hover:scale-110 transition-all duration-200 shadow-soft">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <button @click="nextImage()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700 hover:bg-white hover:scale-110 transition-all duration-200 shadow-soft">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                    @endif

                    {{-- Fullscreen Button --}}
                    @if ($showFullscreen)
                        <button @click="openFullscreen()"
                                class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700 hover:bg-white hover:scale-110 transition-all duration-200 shadow-soft">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4">
                                </path>
                            </svg>
                        </button>
                    @endif

                    {{-- Image Counter --}}
                    @if ($totalImages > 1)
                        <div
                             class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
                            <span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span>
                        </div>
                    @endif
                </div>

                {{-- Product Badges --}}
                <div class="absolute top-4 left-4 flex flex-col gap-2">
                    @if ($product->is_new ?? false)
                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                            {{ __('New') }}
                        </span>
                    @endif
                    @if ($product->sale_price && $product->sale_price < $product->price)
                        <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                            {{ __('Sale') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Thumbnail Navigation --}}
        @if ($showThumbnails && $totalImages > 1)
            <div class="lg:col-span-1">
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Images') }}</h3>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach ($images as $index => $image)
                            <button @click="setCurrentImage({{ $index }})"
                                    :class="currentIndex === {{ $index }} ? 'ring-2 ring-blue-500' :
                                        'hover:ring-2 hover:ring-gray-300'"
                                    class="w-full aspect-w-1 aspect-h-1 bg-gray-100 rounded-lg overflow-hidden transition-all duration-200">
                                <img src="{{ $image->url ?? $image }}"
                                     alt="{{ $image->alt ?? ($product->name ?? 'Product Image') }}"
                                     class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Fullscreen Modal --}}
    <div x-show="fullscreenOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black z-50 flex items-center justify-center"
         style="display: none;"
         @click="closeFullscreen()"
         @keydown.escape="closeFullscreen()">

        <div class="relative max-w-7xl max-h-full p-4" @click.stop>
            {{-- Close Button --}}
            <button @click="closeFullscreen()"
                    class="absolute top-4 right-4 z-10 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700 hover:bg-white hover:scale-110 transition-all duration-200 shadow-soft">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            {{-- Fullscreen Image --}}
            <div class="relative">
                <img :src="currentImage.url"
                     :alt="currentImage.alt"
                     class="max-w-full max-h-full object-contain">

                {{-- Navigation Arrows --}}
                @if ($totalImages > 1)
                    <button @click="previousImage()"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700 hover:bg-white hover:scale-110 transition-all duration-200 shadow-soft">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>

                    <button @click="nextImage()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-700 hover:bg-white hover:scale-110 transition-all duration-200 shadow-soft">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>
                @endif

                {{-- Image Counter --}}
                @if ($totalImages > 1)
                    <div
                         class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/50 text-white px-4 py-2 rounded-full">
                        <span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function productGallery() {
        return {
            images: {{ $images->map(function ($img) {return ['url' => $img->url ?? $img, 'alt' => $img->alt ?? 'Product Image'];})->toJson() }},
            currentIndex: 0,
            showZoom: false,
            zoomEnabled: {{ $showZoom ? 'true' : 'false' }},
            zoomX: 0,
            zoomY: 0,
            fullscreenOpen: false,
            autoplayInterval: null,

            get currentImage() {
                return this.images[this.currentIndex] || this.images[0];
            },

            init() {
                // Initialize with first image
                if (this.images.length > 0) {
                    this.currentIndex = 0;
                }

                // Start autoplay if enabled
                @if ($autoplay)
                    this.startAutoplay();
                @endif

                // Keyboard navigation
                document.addEventListener('keydown', (e) => {
                    if (this.fullscreenOpen) {
                        if (e.key === 'ArrowLeft') this.previousImage();
                        if (e.key === 'ArrowRight') this.nextImage();
                        if (e.key === 'Escape') this.closeFullscreen();
                    }
                });
            },

            setCurrentImage(index) {
                this.currentIndex = index;
                this.stopAutoplay();
            },

            nextImage() {
                this.currentIndex = (this.currentIndex + 1) % this.images.length;
                this.stopAutoplay();
            },

            previousImage() {
                this.currentIndex = this.currentIndex === 0 ? this.images.length - 1 : this.currentIndex - 1;
                this.stopAutoplay();
            },

            openFullscreen() {
                this.fullscreenOpen = true;
                document.body.style.overflow = 'hidden';
            },

            closeFullscreen() {
                this.fullscreenOpen = false;
                document.body.style.overflow = '';
            },

            handleMouseMove(event) {
                if (!this.zoomEnabled) return;

                const rect = event.target.getBoundingClientRect();
                this.zoomX = event.clientX - rect.left;
                this.zoomY = event.clientY - rect.top;
            },

            startAutoplay() {
                this.autoplayInterval = setInterval(() => {
                    this.nextImage();
                }, {{ $autoplayInterval }});
            },

            stopAutoplay() {
                if (this.autoplayInterval) {
                    clearInterval(this.autoplayInterval);
                    this.autoplayInterval = null;
                }
            }
        }
    }
</script>
