<div class="bg-white rounded-lg shadow-sm border overflow-hidden hover:shadow-md transition-shadow duration-200">
    <!-- Product Image -->
    <div class="aspect-square bg-gray-100 relative overflow-hidden">
        <img 
            src="{{ $this->imageUrl }}" 
            alt="{{ $product->name }}"
            class="w-full h-full object-cover"
        >
        
        <!-- Discount Badge -->
        @if($this->discountPercentage)
            <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 text-xs font-bold rounded">
                -{{ $this->discountPercentage }}%
            </div>
        @endif

        <!-- Featured Badge -->
        @if($product->is_featured)
            <div class="absolute top-2 right-2 bg-blue-500 text-white px-2 py-1 text-xs font-bold rounded">
                POPULIARU
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="absolute bottom-2 right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button 
                wire:click="addToWishlist"
                class="p-2 bg-white rounded-full shadow-md hover:bg-gray-50"
                title="Pridėti į pageidavimus"
            >
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Product Info -->
    <div class="p-4">
        <!-- Brand -->
        @if($product->brand)
            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">
                {{ $product->brand->name }}
            </div>
        @endif

        <!-- Product Name -->
        <h3 class="text-sm font-medium text-gray-900 mb-2 line-clamp-2">
            <a href="{{ route('product.show', $product->slug) }}" class="hover:text-blue-600">
                {{ $product->name }}
            </a>
        </h3>

        <!-- SKU -->
        <div class="text-xs text-gray-500 mb-2">
            SKU: {{ $product->sku }}
        </div>

        <!-- Price -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
                <span class="text-lg font-bold text-gray-900">
                    €{{ number_format($this->currentPrice, 2) }}
                </span>
                @if($this->originalPrice)
                    <span class="text-sm text-gray-500 line-through">
                        €{{ number_format($this->originalPrice, 2) }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Stock Status -->
        <div class="mb-3">
            @if($product->manage_stock)
                @if($product->stock_quantity > 0)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Yra sandėlyje ({{ $product->stock_quantity }})
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        Nėra sandėlyje
                    </span>
                @endif
            @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Užsakoma
                </span>
            @endif
        </div>

        <!-- Add to Cart Button -->
        <button 
            wire:click="addToCart"
            @if($product->manage_stock && $product->stock_quantity <= 0) disabled @endif
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-md transition-colors duration-200"
        >
            @if($product->manage_stock && $product->stock_quantity <= 0)
                Nėra sandėlyje
            @else
                Pridėti į krepšelį
            @endif
        </button>
    </div>
</div>
