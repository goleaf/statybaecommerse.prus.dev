<div>
    <x-container class="py-8">
        {{-- Page Header --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('translations.product_images') }}
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                {{ __('translations.product_gallery_description') }}
            </p>
            
            {{-- Statistics --}}
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-2xl mx-auto">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-blue-600">{{ $this->products->total() }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('translations.products') }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-green-600">{{ $this->totalImages }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('translations.total_images') }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-purple-600">{{ $this->generatedImages }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('translations.generated_images') }}</div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mb-8 bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex flex-col md:flex-row gap-4">
                {{-- Search --}}
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('translations.search') }}
                    </label>
                    <input 
                        type="text" 
                        id="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('translations.search_products') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    />
                </div>

                {{-- Filter --}}
                <div class="md:w-48">
                    <label for="filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('translations.filter') }}
                    </label>
                    <select 
                        id="filter"
                        wire:model.live="filter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        <option value="all">{{ __('translations.all_products') }}</option>
                        <option value="with_images">{{ __('translations.with_images') }}</option>
                        <option value="generated_only">{{ __('translations.generated_only') }}</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Products Grid --}}
        @if($this->products->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach($this->products as $product)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        {{-- Product Image Gallery --}}
                        <div class="aspect-square bg-gray-100 dark:bg-gray-700 relative">
                            @if($product->hasImages())
                                @php 
                                    $images = $product->getGalleryImages();
                                    $mainImage = $images[0];
                                @endphp
                                <img 
                                    src="{{ $mainImage['md'] }}"
                                    srcset="{{ $mainImage['xs'] }} 150w, {{ $mainImage['sm'] }} 300w, {{ $mainImage['md'] }} 500w"
                                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 300px"
                                    alt="{{ $mainImage['alt'] }}"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                />
                                
                                {{-- Image Count Badge --}}
                                @if(count($images) > 1)
                                    <div class="absolute top-2 left-2 bg-black/60 text-white px-2 py-1 rounded text-xs">
                                        {{ count($images) }} {{ __('translations.images') }}
                                    </div>
                                @endif

                                {{-- WebP Badge --}}
                                <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded text-xs">
                                    WebP
                                </div>

                                {{-- Generated Badge --}}
                                @if($mainImage['generated'] ?? false)
                                    <div class="absolute bottom-2 right-2 bg-blue-500 text-white px-2 py-1 rounded text-xs">
                                        {{ __('translations.random_image') }}
                                    </div>
                                @endif
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Product Info --}}
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                <a href="{{ route('products.show', $product) }}" 
                                   class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            
                            @if($product->brand)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                    {{ $product->brand->name }}
                                </p>
                            @endif

                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-gray-900 dark:text-white">
                                    €{{ number_format($product->price, 2) }}
                                </span>
                                
                                @if($product->hasImages())
                                    <span class="text-sm text-green-600 dark:text-green-400 font-medium">
                                        {{ $product->getImagesCount() }} {{ __('translations.images') }}
                                    </span>
                                @endif
                            </div>

                            {{-- All Images Preview --}}
                            @if($product->hasImages() && count($product->getGalleryImages()) > 1)
                                <div class="mt-3 flex gap-1 overflow-x-auto">
                                    @foreach(array_slice($product->getGalleryImages(), 1, 4) as $image)
                                        <img 
                                            src="{{ $image['xs'] }}"
                                            alt="{{ $image['alt'] }}"
                                            class="w-12 h-12 rounded object-cover flex-shrink-0"
                                            loading="lazy"
                                        />
                                    @endforeach
                                    @if(count($product->getGalleryImages()) > 5)
                                        <div class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">
                                            +{{ count($product->getGalleryImages()) - 5 }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $this->products->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    {{ __('translations.no_products_found') }}
                </h3>
                <p class="text-gray-500 dark:text-gray-400">
                    {{ __('translations.try_different_search') }}
                </p>
            </div>
        @endif
    </x-container>
</div>
