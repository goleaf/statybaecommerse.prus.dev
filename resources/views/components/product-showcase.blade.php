@props([
    'products' => null,
    'title' => null,
    'subtitle' => null,
    'columns' => 4,
    'showFilters' => true,
    'showSorting' => true,
    'showViewToggle' => true,
    'showPagination' => true,
    'perPage' => 12,
    'viewMode' => 'grid', // grid, list
])

@php
    $title = $title ?? __('Products');
    $subtitle = $subtitle ?? __('Discover our amazing collection of products');
    $products = $products ?? collect([]);

    // Grid classes based on columns
    $gridClasses = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
        5 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
        6 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6',
    ];

    $gridClass = $gridClasses[$columns] ?? $gridClasses[4];
@endphp

<div class="product-showcase" x-data="productShowcase()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">{{ $title }}</h1>
                <p class="text-lg text-gray-600">{{ $subtitle }}</p>
            </div>

            {{-- View Toggle --}}
            @if ($showViewToggle)
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">{{ __('View') }}:</span>
                    <div class="flex border border-gray-300 rounded-lg overflow-hidden">
                        <button @click="viewMode = 'grid'"
                                :class="viewMode === 'grid' ? 'bg-blue-600 text-white' :
                                    'bg-white text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-2 text-sm font-medium transition-colors duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                </path>
                            </svg>
                        </button>
                        <button @click="viewMode = 'list'"
                                :class="viewMode === 'list' ? 'bg-blue-600 text-white' :
                                    'bg-white text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-2 text-sm font-medium transition-colors duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Filters and Sorting --}}
        <div class="flex flex-col lg:flex-row gap-6 mb-8">
            {{-- Filters --}}
            @if ($showFilters)
                <div class="lg:w-1/4">
                    <div class="bg-white border border-gray-200 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Filters') }}</h3>

                        {{-- Price Range --}}
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Price Range') }}</h4>
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <input type="number" x-model="filters.priceMin" placeholder="{{ __('Min') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" x-model="filters.priceMax" placeholder="{{ __('Max') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="relative">
                                    <input type="range" x-model="filters.priceMax" min="0" max="1000"
                                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider">
                                </div>
                            </div>
                        </div>

                        {{-- Categories --}}
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Categories') }}</h4>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach (\App\Models\Category::where('is_active', true)->get() as $category)
                                    <label
                                           class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                                        <input type="checkbox" x-model="filters.categories" value="{{ $category->id }}"
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Brands --}}
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Brands') }}</h4>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach (\App\Models\Brand::where('is_active', true)->get() as $brand)
                                    <label
                                           class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                                        <input type="checkbox" x-model="filters.brands" value="{{ $brand->id }}"
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ $brand->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Rating --}}
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Rating') }}</h4>
                            <div class="space-y-2">
                                @for ($i = 5; $i >= 1; $i--)
                                    <label
                                           class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                                        <input type="radio" x-model="filters.rating" value="{{ $i }}"
                                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <div class="flex items-center gap-1">
                                            @for ($j = 1; $j <= 5; $j++)
                                                <svg class="w-4 h-4 {{ $j <= $i ? 'text-yellow-400' : 'text-gray-300' }}"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                            <span class="text-sm text-gray-700">{{ $i }}
                                                {{ __('Stars & Up') }}</span>
                                        </div>
                                    </label>
                                @endfor
                            </div>
                        </div>

                        {{-- Availability --}}
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Availability') }}</h4>
                            <div class="space-y-2">
                                <label
                                       class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                                    <input type="checkbox" x-model="filters.inStock"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">{{ __('In Stock') }}</span>
                                </label>
                                <label
                                       class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                                    <input type="checkbox" x-model="filters.onSale"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">{{ __('On Sale') }}</span>
                                </label>
                                <label
                                       class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                                    <input type="checkbox" x-model="filters.newArrivals"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">{{ __('New Arrivals') }}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Apply Filters --}}
                        <div class="flex gap-2">
                            <button @click="applyFilters()"
                                    class="flex-1 btn-gradient py-2 rounded-lg font-medium text-sm">
                                {{ __('Apply Filters') }}
                            </button>
                            <button @click="clearFilters()"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-colors duration-200">
                                {{ __('Clear') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Products Grid/List --}}
            <div class="lg:w-3/4">
                {{-- Sorting and Results --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div class="text-sm text-gray-600">
                        {{ __('Showing') }} <span class="font-medium">{{ $products->count() }}</span>
                        {{ __('products') }}
                    </div>

                    @if ($showSorting)
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">{{ __('Sort by') }}:</span>
                            <select x-model="sortBy" @change="applySorting()"
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="relevance">{{ __('Relevance') }}</option>
                                <option value="price_asc">{{ __('Price: Low to High') }}</option>
                                <option value="price_desc">{{ __('Price: High to Low') }}</option>
                                <option value="name_asc">{{ __('Name: A to Z') }}</option>
                                <option value="name_desc">{{ __('Name: Z to A') }}</option>
                                <option value="rating_desc">{{ __('Highest Rated') }}</option>
                                <option value="newest">{{ __('Newest First') }}</option>
                                <option value="popularity">{{ __('Most Popular') }}</option>
                            </select>
                        </div>
                    @endif
                </div>

                {{-- Products Display --}}
                @if ($products->count() > 0)
                    {{-- Grid View --}}
                    <div x-show="viewMode === 'grid'"
                         class="grid {{ $gridClass }} gap-6 mb-8">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    {{-- List View --}}
                    <div x-show="viewMode === 'list'" class="space-y-4 mb-8" style="display: none;">
                        @foreach ($products as $product)
                            <div
                                 class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-medium transition-shadow duration-200">
                                <div class="flex flex-col md:flex-row gap-6">
                                    {{-- Product Image --}}
                                    <div class="md:w-48 flex-shrink-0">
                                        <div class="aspect-w-1 aspect-h-1 bg-gray-100 rounded-lg overflow-hidden">
                                            <img src="{{ $product->getFirstMediaUrl('images') ?? asset('images/placeholder-product.jpg') }}"
                                                 alt="{{ $product->name }}"
                                                 class="w-full h-48 object-cover">
                                        </div>
                                    </div>

                                    {{-- Product Details --}}
                                    <div class="flex-1">
                                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                            <div class="flex-1">
                                                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                                    <a href="{{ route('product.show', $product->slug ?? $product) }}"
                                                       class="hover:text-blue-600 transition-colors duration-200">
                                                        {{ $product->name }}
                                                    </a>
                                                </h3>

                                                @if ($product->brand)
                                                    <p class="text-sm text-gray-600 mb-2">{{ $product->brand->name }}
                                                    </p>
                                                @endif

                                                <p class="text-gray-700 mb-4 line-clamp-3">
                                                    {{ Str::limit($product->description, 200) }}
                                                </p>

                                                {{-- Rating --}}
                                                @if ($product->avg_rating > 0)
                                                    <div class="flex items-center gap-2 mb-4">
                                                        <div class="flex items-center">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <svg class="w-4 h-4 {{ $i <= $product->avg_rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                                                     fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                </svg>
                                                            @endfor
                                                        </div>
                                                        <span
                                                              class="text-sm text-gray-600">({{ $product->reviews_count ?? 0 }})</span>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Price and Actions --}}
                                            <div class="lg:w-48 flex-shrink-0">
                                                <div class="text-right mb-4">
                                                    @if ($product->sale_price && $product->sale_price < $product->price)
                                                        <div class="flex items-center justify-end gap-2">
                                                            <span class="text-2xl font-bold text-gray-900">
                                                                {{ \Illuminate\Support\Number::currency($product->sale_price, current_currency(), app()->getLocale()) }}
                                                            </span>
                                                            <span class="text-lg text-gray-500 line-through">
                                                                {{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <span class="text-2xl font-bold text-gray-900">
                                                            {{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="space-y-2">
                                                    <button wire:click="addToCart({{ $product->id }})"
                                                            @if ($product->stock_quantity <= 0) disabled @endif
                                                            class="w-full btn-gradient py-2 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                                        {{ __('Add to Cart') }}
                                                    </button>

                                                    <button wire:click="toggleWishlist({{ $product->id }})"
                                                            wire:confirm="{{ __('translations.confirm_toggle_wishlist') }}"
                                                            class="w-full border border-gray-300 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors duration-200">
                                                        {{ __('Add to Wishlist') }}
                                                    </button>

                                                    <button @click="openQuickView({{ $product->id }})"
                                                            class="w-full text-blue-600 hover:text-blue-700 py-2 font-medium">
                                                        {{ __('Quick View') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if ($showPagination && $products->count() >= $perPage)
                        <div class="mt-8">
                            <x-pagination :paginator="$products" />
                        </div>
                    @endif
                @else
                    {{-- No Products --}}
                    <div class="text-center py-16">
                        <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ __('No products found') }}</h3>
                        <p class="text-gray-600 mb-8">{{ __('Try adjusting your filters or search terms') }}</p>
                        <button @click="clearFilters()"
                                class="btn-gradient px-8 py-3 rounded-xl font-semibold">
                            {{ __('Clear All Filters') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function productShowcase() {
        return {
            viewMode: '{{ $viewMode }}',
            sortBy: 'relevance',
            filters: {
                priceMin: '',
                priceMax: '',
                categories: [],
                brands: [],
                rating: '',
                inStock: false,
                onSale: false,
                newArrivals: false
            },

            applyFilters() {
                const params = new URLSearchParams();

                if (this.filters.priceMin) params.set('price_min', this.filters.priceMin);
                if (this.filters.priceMax) params.set('price_max', this.filters.priceMax);
                if (this.filters.categories.length > 0) params.set('categories', this.filters.categories.join(','));
                if (this.filters.brands.length > 0) params.set('brands', this.filters.brands.join(','));
                if (this.filters.rating) params.set('rating', this.filters.rating);
                if (this.filters.inStock) params.set('in_stock', '1');
                if (this.filters.onSale) params.set('on_sale', '1');
                if (this.filters.newArrivals) params.set('new_arrivals', '1');

                const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.location.href = newUrl;
            },

            clearFilters() {
                this.filters = {
                    priceMin: '',
                    priceMax: '',
                    categories: [],
                    brands: [],
                    rating: '',
                    inStock: false,
                    onSale: false,
                    newArrivals: false
                };

                window.location.href = window.location.pathname;
            },

            applySorting() {
                const url = new URL(window.location);
                url.searchParams.set('sort', this.sortBy);
                window.location.href = url.toString();
            },

            openQuickView(productId) {
                // Open quick view modal
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4';
                modal.innerHTML = `
                <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">{{ __('Quick View') }}</h3>
                            <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="quick-view-content-${productId}">
                            <div class="flex items-center justify-center py-8">
                                <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                document.body.appendChild(modal);

                // Load product details
                fetch(`/products/${productId}/quick-view`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById(`quick-view-content-${productId}`).innerHTML = html;
                    })
                    .catch(error => {
                        document.getElementById(`quick-view-content-${productId}`).innerHTML =
                            '<div class="text-center py-8 text-red-600">{{ __('Error loading product details') }}</div>';
                    });
            }
        }
    }
</script>
