<div>
    @if ($this->recommendations->isNotEmpty())
        <div class="recommendations-block" 
             data-block-name="{{ $blockName }}"
             data-product-id="{{ $productId }}"
             data-user-id="{{ $userId }}">
            
            @if ($showTitle && $title)
                <div class="recommendations-header mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $title }}
                    </h2>
                </div>
            @endif

            <div class="recommendations-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($this->recommendations as $recommendedProduct)
                    <div class="product-card group relative bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-gray-200 dark:border-gray-700"
                         wire:click="trackView({{ $recommendedProduct->id }})"
                         data-product-id="{{ $recommendedProduct->id }}">
                        
                        <!-- Product Image -->
                        <div class="relative overflow-hidden rounded-t-lg">
                            <a href="{{ route('products.show', $recommendedProduct->slug) }}" 
                               wire:click="trackClick({{ $recommendedProduct->id }})"
                               class="block">
                                <img src="{{ $recommendedProduct->getMainImage() }}" 
                                     alt="{{ $recommendedProduct->name }}"
                                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-200">
                            </a>
                            
                            <!-- Quick Actions -->
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <button type="button"
                                        wire:click="trackClick({{ $recommendedProduct->id }})"
                                        class="bg-white dark:bg-gray-800 rounded-full p-2 shadow-md hover:shadow-lg transition-shadow">
                                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4">
                            <!-- Product Name -->
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2 line-clamp-2">
                                <a href="{{ route('products.show', $recommendedProduct->slug) }}" 
                                   wire:click="trackClick({{ $recommendedProduct->id }})"
                                   class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    {{ $recommendedProduct->name }}
                                </a>
                            </h3>

                            <!-- Brand -->
                            @if ($recommendedProduct->brand)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    {{ $recommendedProduct->brand->name }}
                                </p>
                            @endif

                            <!-- Price -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    @if ($recommendedProduct->compare_price && $recommendedProduct->compare_price > $recommendedProduct->price)
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                                            €{{ number_format($recommendedProduct->price, 2) }}
                                        </span>
                                        <span class="text-sm text-gray-500 line-through">
                                            €{{ number_format($recommendedProduct->compare_price, 2) }}
                                        </span>
                                    @else
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                                            €{{ number_format($recommendedProduct->price, 2) }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Stock Status -->
                                @if ($recommendedProduct->availableQuantity() <= 0)
                                    <span class="text-xs text-red-600 dark:text-red-400 font-medium">
                                        {{ __('frontend.product.out_of_stock') }}
                                    </span>
                                @elseif ($recommendedProduct->availableQuantity() < 10)
                                    <span class="text-xs text-orange-600 dark:text-orange-400 font-medium">
                                        {{ __('frontend.product.low_stock') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Add to Cart Button -->
                            <button type="button"
                                    wire:click="addToCart({{ $recommendedProduct->id }})"
                                    @disabled($recommendedProduct->availableQuantity() <= 0 || $recommendedProduct->shouldHideAddToCart())
                                    class="w-full bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-md transition-colors duration-200 text-sm">
                                @if ($recommendedProduct->availableQuantity() <= 0)
                                    {{ __('frontend.product.out_of_stock') }}
                                @elseif ($recommendedProduct->shouldHideAddToCart())
                                    {{ __('frontend.product.request_quote') }}
                                @else
                                    {{ __('frontend.product.add_to_cart') }}
                                @endif
                            </button>

                            <!-- Recommendation Score (for debugging) -->
                            @if (config('app.debug'))
                                <div class="mt-2 text-xs text-gray-400">
                                    Score: {{ $recommendedProduct->recommendation_score ?? 'N/A' }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                {{ __('frontend.recommendations.no_recommendations') }}
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('frontend.recommendations.no_recommendations_description') }}
            </p>
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>
</div>
