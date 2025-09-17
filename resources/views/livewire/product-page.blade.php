<div class="product-page">
    <!-- Breadcrumb -->
    <nav class="breadcrumb mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="{{ localized_route('home') }}" class="hover:text-gray-700">{{ __('common.home') }}</a></li>
            <li class="flex items-center">
                <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </li>
            @foreach($this->getProductCategories() as $category)
                <li><a href="{{ localized_route('categories.show', $category) }}" class="hover:text-gray-700">{{ $category->name }}</a></li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
            @endforeach
            <li class="text-gray-900 font-medium">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <!-- Product Images -->
        <div class="product-images">
            <div class="main-image mb-4">
                @if($this->getProductImages()->isNotEmpty())
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer" 
                         wire:click="openImageModal(0)">
                        <img src="{{ $this->getProductImages()->first()->getUrl() }}" 
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                    </div>
                @else
                    <div class="aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
            </div>

            @if($this->getProductImages()->count() > 1)
                <div class="thumbnail-images grid grid-cols-4 gap-2">
                    @foreach($this->getProductImages()->take(4) as $index => $image)
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-blue-500 transition-all duration-200"
                             wire:click="openImageModal({{ $index }})">
                            <img src="{{ $image->getUrl('thumb') }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Product Information -->
        <div class="product-info">
            <div class="mb-4">
                @if($this->getProductBrand())
                    <p class="text-sm text-gray-600 mb-2">{{ $this->getProductBrand()->name }}</p>
                @endif
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                <p class="text-sm text-gray-500">SKU: {{ $product->sku }}</p>
            </div>

            <!-- Rating -->
            @if($this->getProductReviewsCount() > 0)
                <div class="rating mb-4">
                    <div class="flex items-center">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $this->getProductRating() ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            @endfor
                        </div>
                        <span class="ml-2 text-sm text-gray-600">
                            {{ number_format($this->getProductRating(), 1) }} ({{ $this->getProductReviewsCount() }} {{ __('products.reviews') }})
                        </span>
                    </div>
                </div>
            @endif

            <!-- Price -->
            <div class="price mb-6">
                @php
                    $priceRange = $this->getProductPriceRange();
                @endphp
                
                @if($priceRange['min'] === $priceRange['max'])
                    <span class="text-3xl font-bold text-gray-900">€{{ number_format($priceRange['min'], 2) }}</span>
                @else
                    <span class="text-3xl font-bold text-gray-900">
                        €{{ number_format($priceRange['min'], 2) }} - €{{ number_format($priceRange['max'], 2) }}
                    </span>
                @endif
                
                @if($product->compare_price && $product->compare_price > $product->price)
                    <span class="text-lg text-gray-500 line-through ml-2">
                        €{{ number_format($product->compare_price, 2) }}
                    </span>
                @endif
            </div>

            <!-- Stock Status -->
            <div class="stock-status mb-6">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $this->getProductStockStatus() === 'in_stock' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $this->getProductStockStatus() === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $this->getProductStockStatus() === 'out_of_stock' ? 'bg-red-100 text-red-800' : '' }}
                ">
                    {{ $this->getProductStockMessage() }}
                </span>
            </div>

            <!-- Short Description -->
            @if($product->short_description)
                <div class="short-description mb-6">
                    <p class="text-gray-700">{{ $product->short_description }}</p>
                </div>
            @endif

            <!-- Variant Selector -->
            @if($product->type === 'variable' && $this->getProductVariants()->isNotEmpty())
                <div class="variant-selector mb-6">
                    <livewire:product-variant-selector :product="$product" />
                </div>
            @else
                <!-- Simple Product Add to Cart -->
                <div class="simple-product-actions mb-6">
                    <div class="flex items-center gap-4">
                        <div class="quantity-selector flex items-center border border-gray-300 rounded-lg">
                            <button type="button" class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <input type="number" value="1" min="1" class="w-16 px-3 py-2 text-center border-0 focus:ring-0 focus:outline-none">
                            <button type="button" class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <button type="button" class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                            </svg>
                            {{ __('products.actions.add_to_cart') }}
                        </button>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="action-buttons flex gap-4 mb-8">
                <button type="button" wire:click="addToWishlist" 
                        class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    {{ __('products.actions.add_to_wishlist') }}
                </button>
                
                <button type="button" wire:click="shareProduct"
                        class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                    </svg>
                    {{ __('products.actions.share') }}
                </button>
            </div>

            <!-- Product Details -->
            <div class="product-details">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button type="button" wire:click="setActiveTab('description')"
                                class="py-2 px-1 border-b-2 font-medium text-sm
                                    {{ $activeTab === 'description' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('products.tabs.description') }}
                        </button>
                        
                        <button type="button" wire:click="setActiveTab('specifications')"
                                class="py-2 px-1 border-b-2 font-medium text-sm
                                    {{ $activeTab === 'specifications' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('products.tabs.specifications') }}
                        </button>
                        
                        <button type="button" wire:click="setActiveTab('reviews')"
                                class="py-2 px-1 border-b-2 font-medium text-sm
                                    {{ $activeTab === 'reviews' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('products.tabs.reviews') }} ({{ $this->getProductReviewsCount() }})
                        </button>
                    </nav>
                </div>

                <div class="py-6">
                    @if($activeTab === 'description')
                        <div class="prose max-w-none">
                            {!! $product->description !!}
                        </div>
                    @elseif($activeTab === 'specifications')
                        <div class="specifications">
                            @if($this->getProductAttributes()->isNotEmpty())
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    @foreach($this->getProductAttributes() as $attribute)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ $attribute->name }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                {{ $attribute->values->pluck('value')->join(', ') }}
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @else
                                <p class="text-gray-500">{{ __('products.messages.no_specifications') }}</p>
                            @endif
                        </div>
                    @elseif($activeTab === 'reviews')
                        <div class="reviews">
                            @if($this->getProductReviewsCount() > 0)
                                <!-- Reviews content here -->
                                <p class="text-gray-500">{{ __('products.messages.reviews_coming_soon') }}</p>
                            @else
                                <p class="text-gray-500">{{ __('products.messages.no_reviews') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->isNotEmpty())
        <div class="related-products mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('products.related_products') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="product-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <a href="{{ localized_route('products.show', $relatedProduct) }}" class="block">
                            <div class="aspect-square bg-gray-100">
                                @if($relatedProduct->hasMedia('images'))
                                    <img src="{{ $relatedProduct->getFirstMediaUrl('images', 'thumb') }}" 
                                         alt="{{ $relatedProduct->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-medium text-gray-900 mb-2 line-clamp-2">{{ $relatedProduct->name }}</h3>
                                <p class="text-lg font-bold text-gray-900">€{{ number_format($relatedProduct->price, 2) }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recently Viewed -->
    @if($recentlyViewed->isNotEmpty())
        <div class="recently-viewed">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('products.recently_viewed') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($recentlyViewed as $recentProduct)
                    <div class="product-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <a href="{{ localized_route('products.show', $recentProduct) }}" class="block">
                            <div class="aspect-square bg-gray-100">
                                @if($recentProduct->hasMedia('images'))
                                    <img src="{{ $recentProduct->getFirstMediaUrl('images', 'thumb') }}" 
                                         alt="{{ $recentProduct->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-medium text-gray-900 mb-2 line-clamp-2">{{ $recentProduct->name }}</h3>
                                <p class="text-lg font-bold text-gray-900">€{{ number_format($recentProduct->price, 2) }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Image Modal -->
    @if($showImageModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:click="closeImageModal">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeImageModal"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        @if($this->getProductImages()->isNotEmpty())
                            <img src="{{ $this->getProductImages()->get($selectedImageIndex)->getUrl() }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-auto">
                        @endif
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeImageModal"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('common.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
