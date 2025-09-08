<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
                    {{ __('translations.hero_title') }}
                </h1>
                <p class="text-xl md:text-2xl text-gray-200 mb-8 max-w-3xl mx-auto">
                    {{ __('translations.hero_subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('products.index') }}" class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-colors shadow-lg">
                        {{ __('translations.shop_now') }}
                    </a>
                    <a href="{{ route('categories.index') }}" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-indigo-600 transition-colors">
                        {{ __('translations.browse_categories') }}
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Stats Bar -->
        <div class="relative bg-white bg-opacity-10 backdrop-blur-sm border-t border-white border-opacity-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-8 text-center">
                    <div class="text-white">
                        <div class="text-2xl md:text-3xl font-bold">{{ number_format($stats['products_count']) }}</div>
                        <div class="text-sm md:text-base opacity-80">{{ __('translations.products') }}</div>
                    </div>
                    <div class="text-white">
                        <div class="text-2xl md:text-3xl font-bold">{{ number_format($stats['categories_count']) }}</div>
                        <div class="text-sm md:text-base opacity-80">{{ __('translations.categories') }}</div>
                    </div>
                    <div class="text-white">
                        <div class="text-2xl md:text-3xl font-bold">{{ number_format($stats['brands_count']) }}</div>
                        <div class="text-sm md:text-base opacity-80">{{ __('translations.brands') }}</div>
                    </div>
                    <div class="text-white">
                        <div class="text-2xl md:text-3xl font-bold">{{ number_format($stats['reviews_count']) }}</div>
                        <div class="text-sm md:text-base opacity-80">{{ __('translations.reviews') }}</div>
                    </div>
                    <div class="text-white col-span-2 md:col-span-1">
                        <div class="text-2xl md:text-3xl font-bold">{{ number_format($stats['avg_rating'], 1) }}/5</div>
                        <div class="text-sm md:text-base opacity-80">{{ __('ecommerce.avg_rating') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    @if($featuredCategories->count() > 0)
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ __('translations.featured_categories') }}</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ __('translations.categories_description') }}</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                @foreach($featuredCategories as $category)
                <a href="{{ route('categories.show', $category) }}" class="group">
                    <div class="bg-gray-100 rounded-2xl p-6 text-center hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                        @if($category->icon)
                        <div class="w-12 h-12 mx-auto mb-4 text-indigo-600">
                            @svg($category->icon, 'w-12 h-12')
                        </div>
                        @elseif($category->getFirstMediaUrl('images'))
                        <img src="{{ $category->getFirstMediaUrl('images', 'thumb') }}" alt="{{ $category->name }}" class="w-12 h-12 mx-auto mb-4 rounded-lg object-cover">
                        @else
                        <div class="w-12 h-12 mx-auto mb-4 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 11h10" />
                            </svg>
                        </div>
                        @endif
                        
                        <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $category->products_count }} {{ __('translations.products') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Latest Products -->
    @if($latestProducts->count() > 0)
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ __('translations.latest_products') }}</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ __('translations.latest_products_subtitle') }}</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($latestProducts as $product)
                <div class="bg-white rounded-2xl border border-gray-200 hover:border-indigo-300 transition-colors duration-300 overflow-hidden group">
                    <div class="aspect-w-1 aspect-h-1 bg-gray-200 relative overflow-hidden">
                        @if($product->getFirstMediaUrl('images'))
                        <img src="{{ $product->getFirstMediaUrl('images', 'medium') }}" alt="{{ $product->name }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        @endif
                        
                        <div class="absolute top-2 left-2 bg-green-400 text-green-900 px-2 py-1 rounded-full text-xs font-semibold">
                            {{ __('shared.new') }}
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold text-gray-900 line-clamp-2">
                                <a href="{{ route('products.show', $product->slug ?? $product) }}" class="hover:text-indigo-600">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            @if($product->brand)
                            <span class="text-xs text-gray-500 ml-2">{{ $product->brand->name }}</span>
                            @endif
                        </div>
                        
                        <div class="flex justify-between items-center mt-4">
                            <div class="flex flex-col">
                                <span class="text-lg font-bold text-gray-900">{{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}</span>
                                @if($product->stock_quantity > 0)
                                <span class="text-xs text-green-600">{{ __('translations.in_stock') }}</span>
                                @else
                                <span class="text-xs text-red-600">{{ __('translations.out_of_stock') }}</span>
                                @endif
                            </div>
                            
                            <button 
                                wire:click="addToCart({{ $product->id }})"
                                @if($product->stock_quantity <= 0) disabled @endif
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                            >
                                {{ __('shared.add_to_cart') }}
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Featured Brands -->
    @if($featuredBrands->count() > 0)
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ __('translations.trusted_brands') }}</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ __('translations.brands_description') }}</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                @foreach($featuredBrands as $brand)
                <a href="{{ route('brands.show', $brand) }}" class="group">
                    <div class="bg-white rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 group-hover:scale-105 border border-gray-200">
                        @if($brand->getFirstMediaUrl('images'))
                        <img src="{{ $brand->getFirstMediaUrl('images', 'thumb') }}" alt="{{ $brand->name }}" class="w-16 h-16 mx-auto mb-3 rounded-lg object-contain">
                        @else
                        <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-lg flex items-center justify-center">
                            <span class="text-lg font-bold text-gray-600">{{ substr($brand->name, 0, 2) }}</span>
                        </div>
                        @endif
                        <h3 class="font-semibold text-gray-900 text-sm">{{ $brand->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $brand->products_count }} {{ __('translations.products') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Latest Reviews -->
    @if($latestReviews->count() > 0)
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ __('translations.customer_testimonials') }}</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ __('translations.customer_reviews_subtitle') }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($latestReviews as $review)
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
                    <div class="flex items-center mb-4">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            @endfor
                        </div>
                        <span class="ml-2 text-sm text-gray-600">{{ $review->rating }}/5</span>
                    </div>
                    
                    <p class="text-gray-700 mb-4 line-clamp-3">{{ $review->content }}</p>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $review->user?->name ?? __('translations.anonymous') }}</p>
                            <p class="text-xs text-gray-500">{{ $review->created_at->format('M j, Y') }}</p>
                        </div>
                        @if($review->product)
                        <a href="{{ route('products.show', $review->product->slug ?? $review->product) }}" class="text-xs text-indigo-600 hover:text-indigo-800">
                            {{ Str::limit($review->product->name, 30) }}
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Newsletter Section -->
    <section class="py-16 bg-indigo-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ __('translations.newsletter_title') }}</h2>
            <p class="text-xl text-indigo-200 mb-8">{{ __('translations.newsletter_subtitle') }}</p>
            
            <form class="max-w-md mx-auto flex gap-4">
                <input 
                    type="email" 
                    placeholder="{{ __('translations.email_placeholder') }}" 
                    class="flex-1 px-4 py-3 rounded-lg border-0 focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600"
                    required
                >
                <button 
                    type="submit" 
                    class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
                >
                    {{ __('translations.subscribe') }}
                </button>
            </form>
            
            <p class="text-sm text-indigo-200 mt-4">
                {{ __('translations.privacy_unsubscribe_notice') }}
            </p>
        </div>
    </section>
</div>
