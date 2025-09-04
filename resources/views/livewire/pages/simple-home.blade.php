<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
                    {{ __('Welcome to') }} <span class="text-yellow-300">{{ config('app.name') }}</span>
                </h1>
                <p class="text-xl md:text-2xl text-gray-200 mb-8 max-w-3xl mx-auto">
                    {{ __('Discover amazing products with the best quality and prices. Your satisfaction is our priority.') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('products.index') }}"
                       class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-colors shadow-lg">
                        {{ __('Shop Now') }}
                    </a>
                    <a href="{{ route('categories.index') }}"
                       class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-indigo-600 transition-colors">
                        {{ __('Browse Categories') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="relative bg-white bg-opacity-10 backdrop-blur-sm border-t border-white border-opacity-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div class="text-white">
                        <div class="text-2xl md:text-3xl font-bold">{{ number_format($stats['products_count']) }}</div>
                        <div class="text-sm md:text-base opacity-80">{{ __('Products') }}</div>
                    </div>
                    <div class="text-white">
                        <div class="text-2xl md:text-3xl font-bold">{{ number_format($stats['categories_count']) }}
                        </div>
                        <div class="text-sm md:text-base opacity-80">{{ __('Categories') }}</div>
                    </div>
                    <div class="text-white">
                        <div class="text-2xl md:text-3xl font-bold">{{ number_format($stats['brands_count']) }}</div>
                        <div class="text-sm md:text-base opacity-80">{{ __('Brands') }}</div>
                    </div>
                    <div class="text-white">
                        <div class="text-2xl md:text-3xl font-bold">{{ number_format($stats['reviews_count']) }}</div>
                        <div class="text-sm md:text-base opacity-80">{{ __('Reviews') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    @if ($featuredProducts->count() > 0)
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ __('Featured Products') }}</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ __('Hand-picked products just for you') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($featuredProducts as $product)
                        <div
                             class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden group">
                            <div class="aspect-w-1 aspect-h-1 bg-gray-200 relative overflow-hidden">
                                @if ($product->getFirstMediaUrl('images'))
                                    <img src="{{ $product->getFirstMediaUrl('images', 'medium') }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif

                                @if ($product->is_featured)
                                    <div
                                         class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 px-2 py-1 rounded-full text-xs font-semibold">
                                        {{ __('Featured') }}
                                    </div>
                                @endif
                            </div>

                            <div class="p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-semibold text-gray-900 line-clamp-2">
                                        <a href="{{ route('products.show', $product->slug) }}"
                                           class="hover:text-indigo-600">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                    @if ($product->brand)
                                        <span class="text-xs text-gray-500 ml-2">{{ $product->brand->name }}</span>
                                    @endif
                                </div>

                                <div class="flex justify-between items-center mt-4">
                                    <div class="flex flex-col">
                                        <span
                                              class="text-lg font-bold text-gray-900">€{{ number_format($product->price, 2) }}</span>
                                        @if ($product->stock_quantity > 0)
                                            <span class="text-xs text-green-600">{{ __('In stock') }}</span>
                                        @else
                                            <span class="text-xs text-red-600">{{ __('Out of stock') }}</span>
                                        @endif
                                    </div>

                                    <a href="{{ route('products.show', $product->slug) }}"
                                       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                                        {{ __('View Product') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-12">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                        {{ __('View All Products') }}
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @else
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __('No Featured Products Yet') }}</h2>
                <p class="text-lg text-gray-600">{{ __('Check back later for featured products') }}</p>
            </div>
        </section>
    @endif
</div>
