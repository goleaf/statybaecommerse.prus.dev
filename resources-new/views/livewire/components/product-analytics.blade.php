<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Product Analytics') }}</h3>
    
    @if($product)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Product Info -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">{{ __('Product Information') }}</h4>
                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">{{ __('Name') }}:</span> {{ $product->name }}</p>
                    <p><span class="font-medium">{{ __('SKU') }}:</span> {{ $product->sku }}</p>
                    <p><span class="font-medium">{{ __('Price') }}:</span> €{{ number_format($product->price, 2) }}</p>
                    <p><span class="font-medium">{{ __('Brand') }}:</span> {{ $product->brand?->name ?? __('N/A') }}</p>
                </div>
            </div>

            <!-- Review Stats -->
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">{{ __('Review Statistics') }}</h4>
                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">{{ __('Total Reviews') }}:</span> {{ $reviewStats['total_reviews'] }}</p>
                    <p><span class="font-medium">{{ __('Average Rating') }}:</span> 
                        <span class="text-yellow-500">{{ number_format($reviewStats['average_rating'], 1) }}/5</span>
                    </p>
                    @if(!empty($reviewStats['rating_distribution']))
                        <div class="mt-2">
                            <p class="font-medium text-xs">{{ __('Rating Distribution') }}:</p>
                            @foreach($reviewStats['rating_distribution'] as $rating => $count)
                                <div class="flex justify-between text-xs">
                                    <span>{{ $rating }} {{ __('stars') }}:</span>
                                    <span>{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Performance Stats -->
            <div class="bg-green-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">{{ __('Performance Metrics') }}</h4>
                <div class="space-y-2 text-sm">
                    <p><span class="font-medium">{{ __('Page Views') }}:</span> {{ $productPerformance['views_count'] }}</p>
                    <p><span class="font-medium">{{ __('Cart Additions') }}:</span> {{ $productPerformance['cart_additions'] }}</p>
                    <p><span class="font-medium">{{ __('Conversion Rate') }}:</span> 
                        <span class="text-green-600">{{ $productPerformance['conversion_rate'] }}%</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if(!empty($relatedProducts))
            <div class="mt-6">
                <h4 class="font-medium text-gray-900 mb-3">{{ __('Related Products') }}</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="bg-white border rounded-lg p-3 hover:shadow-md transition-shadow">
                            @if($relatedProduct['image'])
                                <img src="{{ $relatedProduct['image'] }}" alt="{{ $relatedProduct['name'] }}" 
                                     class="w-full h-24 object-cover rounded mb-2">
                            @endif
                            <h5 class="font-medium text-sm text-gray-900 mb-1">{{ $relatedProduct['name'] }}</h5>
                            <p class="text-sm text-gray-600">€{{ number_format($relatedProduct['price'], 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Top Selling Products -->
        @if(!empty($topSellingProducts))
            <div class="mt-6">
                <h4 class="font-medium text-gray-900 mb-3">{{ __('Top Selling Products') }}</h4>
                <div class="space-y-2">
                    @foreach($topSellingProducts as $topProduct)
                        <div class="flex items-center space-x-3 bg-gray-50 rounded-lg p-3">
                            @if($topProduct['image'])
                                <img src="{{ $topProduct['image'] }}" alt="{{ $topProduct['name'] }}" 
                                     class="w-12 h-12 object-cover rounded">
                            @endif
                            <div class="flex-1">
                                <h5 class="font-medium text-sm text-gray-900">{{ $topProduct['name'] }}</h5>
                                <p class="text-xs text-gray-600">{{ $topProduct['cart_count'] }} {{ __('cart additions') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-8">
            <p class="text-gray-500">{{ __('Product not found') }}</p>
        </div>
    @endif
</div>
