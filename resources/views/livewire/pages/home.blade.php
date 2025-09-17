<div class="min-h-screen bg-gray-50">
    <!-- Enhanced Hero Section with modern design -->
    <section class="relative bg-gradient-hero overflow-hidden min-h-screen flex items-center">
        <div class="absolute inset-0 bg-gray-900/20"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/30 via-purple-600/30 to-blue-600/30"></div>

        <!-- Enhanced animated background elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-500/15 rounded-full blur-3xl animate-float"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-purple-500/15 rounded-full blur-3xl animate-float"
                 style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-pink-500/10 rounded-full blur-3xl animate-float"
                 style="animation-delay: 4s;"></div>
        </div>

        <!-- Floating particles -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-white/30 rounded-full animate-pulse"></div>
            <div class="absolute top-3/4 right-1/4 w-1 h-1 bg-white/40 rounded-full animate-pulse"
                 style="animation-delay: 1s;"></div>
            <div class="absolute top-1/2 right-1/3 w-1.5 h-1.5 bg-white/20 rounded-full animate-pulse"
                 style="animation-delay: 2s;"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="text-center animate-fade-in-up">
                <h1 class="text-5xl md:text-7xl font-heading font-bold text-white mb-6 text-balance">
                    <span class="gradient-text-hero">{{ __('translations.hero_title') }}</span>
                </h1>
                <p class="text-xl md:text-2xl text-white/90 mb-10 max-w-3xl mx-auto text-pretty leading-relaxed">
                    {{ __('translations.hero_subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center items-center animate-fade-in-up"
                     style="animation-delay: 0.3s;">
                    <a href="{{ localized_route('products.index') }}"
                       class="btn-gradient text-lg px-12 py-5 rounded-2xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 group relative overflow-hidden">
                        <span class="relative z-10 flex items-center">
                            {{ __('translations.shop_now') }}
                            <svg class="w-5 h-5 ml-2 inline group-hover:translate-x-1 transition-transform duration-300"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </span>
                    </a>
                    <a href="{{ localized_route('categories.index') }}"
                       class="btn-glass text-lg px-12 py-5 rounded-2xl font-semibold hover:bg-white/20 hover:border-white/50 transition-all duration-300 backdrop-blur-xl group">
                        <span class="flex items-center">
                            {{ __('translations.browse_categories') }}
                            <svg class="w-5 h-5 ml-2 inline group-hover:rotate-12 transition-transform duration-300"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 11h10">
                                </path>
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>


        lalalalallaa


        <!-- Stats Bar -->
        <div class="relative bg-white/10 backdrop-blur-xl border-t border-white/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-8 text-center animate-fade-in-up"
                     style="animation-delay: 0.6s;">
                    <div class="text-white group">
                        <div
                             class="text-3xl md:text-4xl font-bold font-heading mb-2 group-hover:scale-110 transition-transform duration-300">
                            {{ number_format($stats['products_count']) }}</div>
                        <div class="text-sm md:text-base opacity-90 font-medium">{{ __('translations.products') }}</div>
                    </div>
                    <div class="text-white group">
                        <div
                             class="text-3xl md:text-4xl font-bold font-heading mb-2 group-hover:scale-110 transition-transform duration-300">
                            {{ number_format($stats['categories_count']) }}</div>
                        <div class="text-sm md:text-base opacity-90 font-medium">{{ __('translations.categories') }}
                        </div>
                    </div>
                    <div class="text-white group">
                        <div
                             class="text-3xl md:text-4xl font-bold font-heading mb-2 group-hover:scale-110 transition-transform duration-300">
                            {{ number_format($stats['brands_count']) }}</div>
                        <div class="text-sm md:text-base opacity-90 font-medium">{{ __('translations.brands') }}</div>
                    </div>
                    <div class="text-white group">
                        <div
                             class="text-3xl md:text-4xl font-bold font-heading mb-2 group-hover:scale-110 transition-transform duration-300">
                            {{ number_format($stats['reviews_count']) }}</div>
                        <div class="text-sm md:text-base opacity-90 font-medium">{{ __('translations.reviews') }}</div>
                    </div>
                    <div class="text-white col-span-2 md:col-span-1 group">
                        <div
                             class="text-3xl md:text-4xl font-bold font-heading mb-2 group-hover:scale-110 transition-transform duration-300">
                            {{ number_format($stats['avg_rating'], 1) }}/5</div>
                        <div class="text-sm md:text-base opacity-90 font-medium">{{ __('ecommerce.avg_rating') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sidebar + Featured Categories Grid -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-3">
                    <livewire:components.home-sidebar />
                </div>
                <div class="lg:col-span-9">
                    @if ($featuredCategories->count() > 0)
                        <div class="text-center mb-12 animate-fade-in-up">
                            <h2 class="text-4xl md:text-5xl font-heading font-bold text-gray-900 mb-4">
                                {{ __('translations.featured_categories') }}</h2>
                            <p class="text-xl text-gray-600 max-w-3xl mx-auto text-pretty">
                                {{ __('translations.categories_description') }}</p>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                            @foreach ($featuredCategories as $index => $category)
                                <a href="{{ route('localized.categories.show', ['category' => $category->slug ?? $category]) }}"
                                   class="group animate-on-scroll" style="animation-delay: {{ $index * 0.1 }}s;">
                                    <div
                                         class="bg-gradient-to-br from-gray-50 to-white rounded-3xl p-6 text-center hover:shadow-lg transition-all duration-300 group-hover:scale-105 border border-gray-100 group-hover:border-blue-200">
                                        @if ($category->icon)
                                            <div
                                                 class="w-16 h-16 mx-auto mb-4 text-blue-600 group-hover:text-blue-700 transition-colors duration-300">
                                                <x-app-icon :name="$category->icon" class="w-16 h-16" />
                                            </div>
                                        @elseif($category->getFirstMediaUrl('images'))
                                            <img src="{{ $category->getFirstMediaUrl('images', 'thumb') }}"
                                                 alt="{{ $category->name }}"
                                                 class="w-16 h-16 mx-auto mb-4 rounded-2xl object-cover shadow-sm group-hover:shadow-md transition-shadow duration-300">
                                        @else
                                            <div
                                                 class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center group-hover:from-blue-200 group-hover:to-purple-200 transition-all duration-300">
                                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 11h10" />
                                                </svg>
                                            </div>
                                        @endif
                                        <h3
                                            class="font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-300 mb-2">
                                            {{ $category->name }}</h3>
                                        <p class="text-sm text-gray-500 font-medium">{{ $category->products_count }}
                                            {{ __('translations.products') }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Products -->
    @if ($latestProducts->count() > 0)
        <section class="py-20 bg-gradient-to-br from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 animate-fade-in-up">
                    <h2 class="text-4xl md:text-5xl font-heading font-bold text-gray-900 mb-4">
                        {{ __('translations.latest_products') }}</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto text-pretty">
                        {{ __('translations.latest_products_subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach ($latestProducts as $index => $product)
                        <div class="product-card bg-white rounded-3xl border border-gray-200 hover:border-blue-300 transition-all duration-300 overflow-hidden group shadow-sm hover:shadow-lg animate-on-scroll"
                             style="animation-delay: {{ $index * 0.1 }}s;">
                            <div
                                 class="aspect-w-1 aspect-h-1 bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                                @if ($product->getFirstMediaUrl('images'))
                                    <img src="{{ $product->getFirstMediaUrl('images', 'image-md') ?: $product->getFirstMediaUrl('images') }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-64 object-cover product-card-image">
                                @else
                                    <div
                                         class="w-full h-64 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif

                                <div
                                     class="absolute top-4 left-4 bg-gradient-to-r from-green-500 to-green-600 text-white px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm">
                                    {{ __('shared.new') }}
                                </div>

                                @if ($product->brand)
                                    <div
                                         class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm text-gray-700 px-2 py-1 rounded-lg text-xs font-medium shadow-sm">
                                        {{ $product->brand->name }}
                                    </div>
                                @endif
                            </div>

                            <div class="p-6">
                                <h3
                                    class="font-semibold text-gray-900 text-lg mb-3 line-clamp-2 group-hover:text-blue-700 transition-colors duration-300">
                                    <a href="{{ route('product.show', $product->slug) }}"
                                       class="hover:text-blue-700">
                                        {{ $product->name }}
                                    </a>
                                </h3>

                                <div class="flex justify-between items-center">
                                    <div class="flex flex-col">
                                        <span
                                              class="text-2xl font-bold text-gray-900 mb-1">{{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}</span>
                                        @if ($product->stock_quantity > 0)
                                            <span class="text-sm text-green-600 font-medium flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                {{ __('translations.in_stock') }}
                                            </span>
                                        @else
                                            <span class="text-sm text-red-600 font-medium flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                {{ __('translations.out_of_stock') }}
                                            </span>
                                        @endif
                                    </div>

                                    <button wire:click="addToCart({{ $product->id }})"
                                            @if ($product->stock_quantity <= 0) disabled @endif
                                            class="btn-gradient text-sm px-6 py-3 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-300">
                                        {{ __('shared.add_to_cart') }}
                                        <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01">
                                            </path>
                                        </svg>
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
    @if ($featuredBrands->count() > 0)
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 animate-fade-in-up">
                    <h2 class="text-4xl md:text-5xl font-heading font-bold text-gray-900 mb-4">
                        {{ __('translations.trusted_brands') }}</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto text-pretty">
                        {{ __('translations.brands_description') }}</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-6">
                    @foreach ($featuredBrands as $index => $brand)
                        <a href="{{ localized_route('brands.show', $brand) }}" class="group animate-on-scroll"
                           style="animation-delay: {{ $index * 0.1 }}s;">
                            <div
                                 class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-6 text-center hover:shadow-lg transition-all duration-300 group-hover:scale-105 border border-gray-200 group-hover:border-blue-300">
                                @if ($brand->getFirstMediaUrl('images'))
                                    <img src="{{ $brand->getFirstMediaUrl('images', 'thumb') }}"
                                         alt="{{ $brand->name }}"
                                         class="w-20 h-20 mx-auto mb-4 rounded-xl object-contain shadow-sm group-hover:shadow-md transition-shadow duration-300">
                                @else
                                    <div
                                         class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-xl flex items-center justify-center group-hover:from-blue-200 group-hover:to-purple-200 transition-all duration-300">
                                        <span
                                              class="text-xl font-bold text-blue-700">{{ substr($brand->name, 0, 2) }}</span>
                                    </div>
                                @endif
                                <h3
                                    class="font-semibold text-gray-900 text-sm group-hover:text-blue-700 transition-colors duration-300">
                                    {{ $brand->name }}</h3>
                                <p class="text-xs text-gray-500 mt-1 font-medium">{{ $brand->products_count }}
                                    {{ __('translations.products') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Latest Reviews -->
    @if ($latestReviews->count() > 0)
        <section class="py-20 bg-gradient-to-br from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 animate-fade-in-up">
                    <h2 class="text-4xl md:text-5xl font-heading font-bold text-gray-900 mb-4">
                        {{ __('translations.customer_testimonials') }}</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto text-pretty">
                        {{ __('translations.customer_reviews_subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($latestReviews as $index => $review)
                        <div class="bg-white rounded-3xl p-8 border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 animate-on-scroll"
                             style="animation-delay: {{ $index * 0.1 }}s;">
                            <div class="flex items-center mb-6">
                                <div class="flex items-center">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                                <span class="ml-3 text-sm text-gray-600 font-medium">{{ $review->rating }}/5</span>
                            </div>

                            <p class="text-gray-700 mb-6 line-clamp-3 text-pretty leading-relaxed">
                                {{ $review->content }}</p>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                         class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                                        <span
                                              class="text-white font-semibold text-sm">{{ substr($review->user?->name ?? 'A', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm">
                                            {{ $review->user?->name ?? __('translations.anonymous') }}</p>
                                        <p class="text-xs text-gray-500">{{ $review->created_at->format('M j, Y') }}
                                        </p>
                                    </div>
                                </div>
                                @if ($review->product)
                                    <a href="{{ route('product.show', $review->product->slug) }}"
                                       class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
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
    <section class="py-20 bg-gradient-to-br from-blue-600 via-blue-700 to-purple-600 relative overflow-hidden">
        <!-- Background elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-float"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-float"
                 style="animation-delay: 2s;"></div>
        </div>

        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="animate-fade-in-up">
                <h2 class="text-4xl md:text-5xl font-heading font-bold text-white mb-6 text-balance">
                    {{ __('translations.newsletter_title') }}
                </h2>
                <p class="text-xl text-white/90 mb-12 max-w-3xl mx-auto text-pretty">
                    {{ __('translations.newsletter_subtitle') }}
                </p>

                <form class="max-w-lg mx-auto flex flex-col sm:flex-row gap-4 animate-fade-in-up"
                      style="animation-delay: 0.3s;">
                    <input
                           type="email"
                           placeholder="{{ __('translations.email_placeholder') }}"
                           class="flex-1 px-6 py-4 rounded-2xl border-0 focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600 text-gray-900 placeholder:text-gray-500 shadow-lg"
                           required>
                    <button
                            type="submit"
                            class="bg-white text-blue-700 px-8 py-4 rounded-2xl font-semibold hover:bg-gray-50 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                        {{ __('translations.subscribe') }}
                        <svg class="w-5 h-5 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </form>

                <p class="text-sm text-white/80 mt-6 max-w-2xl mx-auto">
                    {{ __('translations.privacy_unsubscribe_notice') }}
                </p>
            </div>
        </div>
    </section>
</div>
