@props([
    'product',
    'showQuickAdd' => false,
    'showWishlist' => false,
    'showCompare' => false,
    'size' => 'md' // sm, md, lg
])

@php
    $cardClasses = match($size) {
        'sm' => 'group relative overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200 transition-all duration-300 hover:shadow-md hover:ring-gray-300 dark:bg-gray-800 dark:ring-gray-700',
        'lg' => 'group relative overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 transition-all duration-300 hover:shadow-xl hover:ring-gray-300 dark:bg-gray-800 dark:ring-gray-700',
        default => 'group relative overflow-hidden rounded-xl bg-white shadow-md ring-1 ring-gray-200 transition-all duration-300 hover:shadow-lg hover:ring-gray-300 dark:bg-gray-800 dark:ring-gray-700'
    };
    
    $imageClasses = match($size) {
        'sm' => 'h-48',
        'lg' => 'h-80',
        default => 'h-64'
    };
    
    $price = $product->prices->first();
    $hasDiscount = $product->sale_price && $price && $product->sale_price < $price->amount;
@endphp

<div class="{{ $cardClasses }}">
    {{-- Product Image --}}
    <div class="aspect-w-1 aspect-h-1 overflow-hidden">
        @if($product->getFirstMediaUrl('gallery'))
            <img 
                src="{{ $product->getFirstMediaUrl('gallery') }}" 
                alt="{{ $product->name }}"
                class="{{ $imageClasses }} w-full object-cover transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
            />
        @else
            <div class="flex {{ $imageClasses }} items-center justify-center bg-gray-200 dark:bg-gray-700">
                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif
        
        {{-- Enhanced Badges with Filament Components --}}
        <div class="absolute top-3 left-3 flex flex-col gap-2">
            @if($product->is_featured)
                <x-filament::badge color="warning" icon="heroicon-o-star">
                    {{ __('Featured') }}
                </x-filament::badge>
            @endif
            
            @if($hasDiscount)
                <x-filament::badge color="danger" icon="heroicon-o-tag">
                    {{ __('Sale') }}
                </x-filament::badge>
            @endif
            
            @if($product->created_at->isAfter(now()->subDays(7)))
                <x-filament::badge color="success" icon="heroicon-o-sparkles">
                    {{ __('New') }}
                </x-filament::badge>
            @endif
        </div>

        {{-- Enhanced Action Buttons with Filament Components --}}
        <div class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            @if($showWishlist)
                <x-filament::icon-button 
                    icon="heroicon-o-heart"
                    color="gray"
                    tooltip="{{ __('Add to Wishlist') }}"
                    class="bg-white/90 shadow-md hover:bg-white hover:text-red-500 transition-all duration-200"
                />
            @endif
            
            @if($showCompare)
                <x-filament::icon-button 
                    icon="heroicon-o-scale"
                    color="gray"
                    tooltip="{{ __('Add to Compare') }}"
                    class="bg-white/90 shadow-md hover:bg-white hover:text-blue-500 transition-all duration-200"
                />
            @endif
        </div>

        {{-- Enhanced Quick View Overlay --}}
        <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            <x-filament::button 
                href="{{ route('products.show', ['slug' => $product->slug, 'locale' => app()->getLocale()]) }}"
                color="white"
                icon="heroicon-o-eye"
                class="shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
            >
                {{ __('Quick View') }}
            </x-filament::button>
        </div>
    </div>
    
    {{-- Product Info --}}
    <div class="p-4">
        @if($product->brand)
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $product->brand->name }}</p>
        @endif
        
        <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white line-clamp-2">
            <a href="{{ route('products.show', ['slug' => $product->slug, 'locale' => app()->getLocale()]) }}" 
               class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
                {{ $product->name }}
            </a>
        </h3>
        
        @if($product->summary)
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                {{ $product->summary }}
            </p>
        @endif
        
        {{-- Rating --}}
        @if($product->reviews_count > 0)
            <div class="mt-2 flex items-center gap-2">
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="h-4 w-4 {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    @endfor
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">({{ $product->reviews_count }})</span>
            </div>
        @endif
        
        {{-- Price and Actions --}}
        <div class="mt-4 flex items-center justify-between">
            @if($price)
                <div class="flex flex-col">
                    @if($hasDiscount)
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold text-red-600 dark:text-red-400">
                                {{ $price->currency->symbol }}{{ number_format($product->sale_price, 2) }}
                            </span>
                            <span class="text-sm text-gray-500 line-through">
                                {{ $price->currency->symbol }}{{ number_format($price->amount, 2) }}
                            </span>
                        </div>
                        <span class="text-xs text-red-600 dark:text-red-400 font-medium">
                            {{ __('Save') }} {{ $price->currency->symbol }}{{ number_format($price->amount - $product->sale_price, 2) }}
                        </span>
                    @else
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $price->currency->symbol }}{{ number_format($price->amount, 2) }}
                        </span>
                    @endif
                </div>
            @endif
            
            @if($showQuickAdd)
                <x-filament::button 
                    wire:click="addToCart({{ $product->id }})"
                    color="primary"
                    icon="heroicon-o-shopping-cart"
                    size="sm"
                    class="transform hover:scale-105 transition-transform duration-200"
                    tooltip="{{ __('cart_add_to_cart') }}"
                >
                    {{ __('cart_add_to_cart') }}
                </x-filament::button>
            @endif
        </div>
        
        {{-- Enhanced Stock Status with Filament Badges --}}
        @if($product->stock_quantity !== null)
            <div class="mt-2">
                @if($product->stock_quantity > 10)
                    <x-filament::badge color="success" icon="heroicon-o-check-circle" size="sm">
                        {{ __('product_in_stock') }}
                    </x-filament::badge>
                @elseif($product->stock_quantity > 0)
                    <x-filament::badge color="warning" icon="heroicon-o-exclamation-triangle" size="sm">
                        {{ __('product_low_stock') }} ({{ $product->stock_quantity }})
                    </x-filament::badge>
                @else
                    <x-filament::badge color="danger" icon="heroicon-o-x-circle" size="sm">
                        {{ __('product_out_of_stock') }}
                    </x-filament::badge>
                @endif
            </div>
        @endif
    </div>
</div>
