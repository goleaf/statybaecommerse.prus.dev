<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                @switch($type)
                    @case('customers_also_bought')
                        {{ __('frontend.recommendations.customers_also_bought') }}
                        @break
                    @case('trending')
                        {{ __('frontend.recommendations.trending_products') }}
                        @break
                    @case('personalized')
                        {{ __('frontend.recommendations.recommended_for_you') }}
                        @break
                    @case('cross_sell')
                        {{ __('frontend.recommendations.frequently_bought_together') }}
                        @break
                    @case('up_sell')
                        {{ __('frontend.recommendations.you_might_like') }}
                        @break
                    @case('popular')
                        {{ __('frontend.recommendations.popular_products') }}
                        @break
                    @case('recently_viewed')
                        {{ __('frontend.recommendations.recently_viewed') }}
                        @break
                    @default
                        {{ __('frontend.recommendations.related_products') }}
                @endswitch
            </h2>
        </div>

        @if ($this->recommendations->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($this->recommendations as $product)
                    <div class="group relative bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200">
                        <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80">
                            @if ($product->getMainImage())
                                <img src="{{ $product->getMainImage() }}" 
                                     alt="{{ $product->name }}"
                                     class="h-full w-full object-cover object-center lg:h-full lg:w-full">
                            @else
                                <div class="h-full w-full bg-gray-200 flex items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                @if($product->brand)
                                    <span class="text-xs text-gray-500 uppercase tracking-wide">
                                        {{ $product->brand->name }}
                                    </span>
                                @endif
                                @if($product->average_rating > 0)
                                    <div class="flex items-center">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($product->average_rating))
                                                    <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-3 h-3 text-gray-300 fill-current" viewBox="0 0 20 20">
                                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                    </svg>
                                                @endif
                                            @endfor
                                        </div>
                                        <span class="ml-1 text-xs text-gray-500">({{ $product->reviews_count }})</span>
                                    </div>
                                @endif
                            </div>
                            
                            <h3 class="text-sm font-medium text-gray-900 mb-2 line-clamp-2">
                                <a href="{{ localized_route('products.show', ['product' => $product->slug]) }}" 
                                   class="hover:text-gray-700"
                                   wire:click="trackRecommendationClick({{ $product->id }}, 'product_click')">
                                    {{ $product->name }}
                                </a>
                            </h3>

                        <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    @if($product->sale_price && $product->sale_price < $product->price)
                                        <span class="text-lg font-semibold text-red-600">
                                            {{ number_format($product->sale_price, 2) }} €
                                        </span>
                                        <span class="text-sm text-gray-500 line-through">
                                            {{ number_format($product->price, 2) }} €
                                        </span>
                                    @else
                                        <span class="text-lg font-semibold text-gray-900">
                                {{ number_format($product->price, 2) }} €
                            </span>
                                    @endif
                                </div>

                                @if(!$product->shouldHideAddToCart())
                            <button wire:click="addToCart({{ $product->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l1.5 6m0 0h6.5"/>
                                        </svg>
                                        {{ __('frontend.buttons.add_to_cart') }}
                            </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    {{ __('recommendations.no_recommendations') }}
                </h3>
                <p class="text-gray-600">
                    {{ __('recommendations.no_recommendations_desc') }}
                </p>
            </div>
        @endif
    </div>
</div>
