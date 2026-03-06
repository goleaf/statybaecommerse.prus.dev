@if($products->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <!-- Product Image -->
                <a href="{{ route('products.show', $product) }}" class="block aspect-w-1 aspect-h-1 bg-gray-200">
                    <img src="{{ $product->getImageUrl('md') ?: asset('images/placeholder-product.jpg') }}" 
                         alt="{{ $product->name }}"
                         class="w-full h-48 object-cover">
                </a>

                <!-- Product Content -->
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                        <a href="{{ route('products.show', $product) }}" class="hover:text-blue-600">
                            {{ $product->name }}
                        </a>
                    </h3>

                    @if($product->description)
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                            {!! Str::limit(strip_tags($product->description), 80) !!}
                        </p>
                    @endif

                    <!-- Price -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-lg font-bold text-gray-900">
                                {{ number_format($product->price, 2) }} €
                            </span>
                        </div>
                        
                        @if($product->stock_quantity > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ __('products.in_stock') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ __('products.out_of_stock') }}
                            </span>
                        @endif
                    </div>

                    <!-- Categories -->
                    @if($product->categories->count() > 0)
                        <div class="mb-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($product->categories->take(2) as $category)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                                @if($product->categories->count() > 2)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        +{{ $product->categories->count() - 2 }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center justify-between">
                        <a href="{{ route('products.show', $product) }}" 
                           class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __('products.view_details') }}
                        </a>
                        
                        @if($product->stock_quantity > 0)
                            <button type="button" 
                                    class="ml-2 cursor-pointer rounded-lg border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50 transition-colors"
                                    onclick="addToCart({{ $product->id }})">
                                {{ __('frontend.home.products.actions.add_to_cart') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $products->links() }}
    </div>
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('collections.no_products_found') }}</h3>
        <p class="text-gray-500">{{ __('collections.try_different_filters') }}</p>
    </div>
@endif
