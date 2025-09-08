@props([
    'product',
    'showQuickAdd' => true,
    'showWishlist' => true,
    'showCompare' => true,
    'showBrand' => true,
    'showRating' => true,
    'layout' => 'grid', // grid, list
])

<div @class([
    'group relative overflow-hidden bg-white shadow-md ring-1 ring-gray-200 transition-all duration-300 hover:shadow-lg hover:ring-gray-300 dark:bg-gray-800 dark:ring-gray-700 dark:hover:ring-gray-600',
    'rounded-xl' => $layout === 'grid',
    'rounded-lg flex' => $layout === 'list',
])>
    {{-- Product Image --}}
    <div @class([
        'aspect-w-1 aspect-h-1 overflow-hidden' => $layout === 'grid',
        'w-48 flex-shrink-0 overflow-hidden' => $layout === 'list',
    ])>
        @if($product->hasImages())
            @php $imageAttrs = $product->getResponsiveImageAttributes('md'); @endphp
            <img 
                src="{{ $imageAttrs['src'] }}" 
                srcset="{{ $imageAttrs['srcset'] }}"
                sizes="{{ $imageAttrs['sizes'] }}"
                alt="{{ $imageAttrs['alt'] }}"
                @class([
                    'h-64 w-full object-cover transition-transform duration-300 group-hover:scale-105' => $layout === 'grid',
                    'h-full w-full object-cover' => $layout === 'list',
                ])
                loading="lazy"
            />
        @else
            <div @class([
                'flex h-64 items-center justify-center bg-gray-200 dark:bg-gray-700' => $layout === 'grid',
                'flex h-full items-center justify-center bg-gray-200 dark:bg-gray-700' => $layout === 'list',
            ])>
                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif

        {{-- Action Buttons Overlay --}}
        @if($showWishlist || $showCompare)
            <div class="absolute top-2 right-2 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                @if($showWishlist)
                    <button 
                        wire:click="toggleWishlist({{ $product->id }})"
                        class="p-2 rounded-full bg-white/90 hover:bg-white text-gray-600 hover:text-red-500 transition-colors duration-200 shadow-sm"
                        title="{{ __('translations.add_to_wishlist') }}"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                @endif
                
                @if($showCompare)
                    <button 
                        wire:click="addToCompare({{ $product->id }})"
                        class="p-2 rounded-full bg-white/90 hover:bg-white text-gray-600 hover:text-blue-500 transition-colors duration-200 shadow-sm"
                        title="{{ __('translations.add_to_compare') }}"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </button>
                @endif
            </div>
        @endif
    </div>
    
    {{-- Product Info --}}
    <div @class([
        'p-4' => $layout === 'grid',
        'flex-1 p-6' => $layout === 'list',
    ])>
        @if($showBrand && $product->brand)
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ $product->brand->name }}</p>
        @endif
        
        <h3 @class([
            'text-lg font-medium text-gray-900 dark:text-white line-clamp-2' => $layout === 'grid',
            'text-xl font-medium text-gray-900 dark:text-white' => $layout === 'list',
        ])>
            <a href="{{ route('products.show', $product->slug ?? $product) }}" 
               class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
                {{ $product->name }}
            </a>
        </h3>
        
        @if($product->summary)
            <p @class([
                'mt-2 text-sm text-gray-600 dark:text-gray-300 line-clamp-2' => $layout === 'grid',
                'mt-2 text-gray-600 dark:text-gray-300' => $layout === 'list',
            ])>
                {{ $product->summary }}
            </p>
        @endif

        @if($showRating && $product->reviews_avg_rating)
            <div class="mt-2 flex items-center">
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="h-4 w-4 {{ $i <= $product->reviews_avg_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>
                <span class="ml-2 text-sm text-gray-500">({{ $product->reviews_count ?? 0 }})</span>
            </div>
        @endif
        
        <div @class([
            'mt-4 flex items-center justify-between' => $layout === 'grid',
            'mt-4 flex items-center justify-between' => $layout === 'list',
        ])>
            @if($product->prices->isNotEmpty())
                <div class="flex items-center space-x-2">
                    @php $price = $product->prices->first(); @endphp
                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $price->currency->symbol }}{{ number_format($price->amount, 2) }}
                    </span>
                    @if($product->sale_price && $product->sale_price < $price->amount)
                        <span class="text-sm text-gray-500 line-through">
                            {{ $price->currency->symbol }}{{ number_format($product->sale_price, 2) }}
                        </span>
                        <x-shared.badge variant="danger" size="sm">
                            {{ __('translations.sale') }}
                        </x-shared.badge>
                    @endif
                </div>
            @endif
            
            @if($showQuickAdd)
                <x-shared.button 
                    wire:click="addToCart({{ $product->id }})"
                    variant="primary"
                    size="sm"
                    icon="heroicon-o-shopping-cart"
                    class="transform hover:scale-105"
                >
                    {{ __('translations.add_to_cart') }}
                </x-shared.button>
            @endif
        </div>
    </div>
</div>
