<div class="group relative bg-white rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-gray-300">
    <!-- Discount Badge -->
    @if($discountPercentage)
        <div class="absolute top-2 left-2 z-10 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
            -{{ $discountPercentage }}%
        </div>
    @endif

    <!-- Wishlist Button -->
    <button 
        wire:click="toggleWishlist"
        class="absolute top-2 right-2 z-10 p-2 rounded-full bg-white/80 hover:bg-white transition-colors duration-200 {{ $isWishlisted ? 'text-red-500' : 'text-gray-400' }}"
        title="{{ $isWishlisted ? __('Remove from wishlist') : __('Add to wishlist') }}"
    >
        <svg class="w-5 h-5" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
        </svg>
    </button>

    <!-- Product Image -->
    <div class="aspect-square overflow-hidden rounded-t-lg bg-gray-100">
        @if($product->getFirstMediaUrl('gallery'))
            <img 
                src="{{ $product->getFirstMediaUrl('gallery', 'thumb') }}" 
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
            >
        @else
            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif

        <!-- Quick View Overlay -->
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
            <button 
                wire:click="toggleQuickView"
                class="bg-white text-gray-900 px-4 py-2 rounded-md font-medium hover:bg-gray-100 transition-colors duration-200"
            >
                {{ __('Quick View') }}
            </button>
        </div>
    </div>

    <!-- Product Info -->
    <div class="p-4">
        <!-- Brand -->
        @if($product->brand)
            <p class="text-xs text-gray-500 mb-1">{{ $product->brand->name }}</p>
        @endif

        <!-- Product Name -->
        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-blue-600 transition-colors">
                {{ $product->name }}
            </a>
        </h3>

        <!-- Rating -->
        @if($reviewCount > 0)
            <div class="flex items-center mb-2">
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= $averageRating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    @endfor
                </div>
                <span class="text-sm text-gray-500 ml-1">({{ $reviewCount }})</span>
            </div>
        @endif

        <!-- Price -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
                @if($selectedVariant)
                    @if($selectedVariant->compare_price)
                        <span class="text-sm text-gray-500 line-through">€{{ number_format($selectedVariant->compare_price, 2) }}</span>
                    @endif
                    <span class="text-lg font-bold text-gray-900">€{{ number_format($selectedVariant->price, 2) }}</span>
                @endif
            </div>
            
            <!-- Stock Status -->
            @if($selectedVariant)
                @if($selectedVariant->stock_quantity > 0)
                    <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">
                        {{ __('In Stock') }} ({{ $selectedVariant->stock_quantity }})
                    </span>
                @else
                    <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded">
                        {{ __('Out of Stock') }}
                    </span>
                @endif
            @endif
        </div>

        <!-- Variants Selector -->
        @if($product->variants->count() > 1)
            <div class="mb-3">
                <button 
                    wire:click="$toggle('showVariants')"
                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
                >
                    {{ __('Variants') }} ({{ $product->variants->count() }})
                    <svg class="w-4 h-4 ml-1 transform {{ $showVariants ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                @if($showVariants)
                    <div class="mt-2 space-y-1">
                        @foreach($product->variants as $variant)
                            <button 
                                wire:click="selectVariant({{ $variant->id }})"
                                class="block w-full text-left text-sm p-2 rounded {{ $selectedVariant?->id === $variant->id ? 'bg-blue-100 text-blue-800' : 'hover:bg-gray-100' }}"
                            >
                                {{ $variant->name }} - €{{ number_format($variant->price, 2) }}
                                @if($variant->stock_quantity <= 0)
                                    <span class="text-red-500">({{ __('Out of Stock') }})</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex space-x-2">
            <button 
                wire:click="addToCart"
                @if(!$selectedVariant || $selectedVariant->stock_quantity <= 0) disabled @endif
                class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors duration-200 disabled:bg-gray-300 disabled:cursor-not-allowed text-sm font-medium"
            >
                {{ __('Add to Cart') }}
            </button>
            
            <button 
                wire:click="addToCompare"
                class="p-2 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-200"
                title="{{ __('Add to Compare') }}"
            >
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Quick View Modal -->
    @if($showQuickView)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ open: true }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" wire:click="toggleQuickView"></div>
                </div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-start">
                            <div class="w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    {{ $product->name }}
                                </h3>
                                
                                <div class="mb-4">
                                    @if($product->getFirstMediaUrl('gallery'))
                                        <img 
                                            src="{{ $product->getFirstMediaUrl('gallery', 'small') }}" 
                                            alt="{{ $product->name }}"
                                            class="w-full h-48 object-cover rounded"
                                        >
                                    @endif
                                </div>

                                <p class="text-gray-600 mb-4">{{ Str::limit($product->description, 150) }}</p>

                                <div class="flex items-center justify-between mb-4">
                                    <div class="text-xl font-bold text-gray-900">
                                        €{{ number_format($selectedVariant?->price ?? 0, 2) }}
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <label class="text-sm text-gray-600">{{ __('Quantity') }}:</label>
                                        <input 
                                            type="number" 
                                            wire:model="quickViewQuantity"
                                            min="1" 
                                            max="{{ $selectedVariant?->stock_quantity ?? 1 }}"
                                            class="w-16 px-2 py-1 border rounded text-center"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            wire:click="quickAddToCart"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            {{ __('Add to Cart') }}
                        </button>
                        <button 
                            wire:click="toggleQuickView"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
