@props([
    'categories' => null,
    'brands' => null,
    'priceRange' => null,
    'filters' => [],
])

@php
    $categories = $categories ?? \App\Models\Category::where('is_active', true)->get();
    $brands = $brands ?? \App\Models\Brand::where('is_active', true)->get();
    $priceRange = $priceRange ?? ['min' => 0, 'max' => 1000];
@endphp

<div class="bg-white border border-gray-200 rounded-xl p-6" x-data="productFilters()">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">{{ __('Filters') }}</h3>
        <button @click="clearAllFilters()"
                class="text-sm text-blue-600 hover:text-blue-700 font-medium">
            {{ __('Clear All') }}
        </button>
    </div>

    <form @submit.prevent="applyFilters" class="space-y-6">
        {{-- Price Range --}}
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Price Range') }}</h4>
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <input type="number"
                           x-model="filters.priceMin"
                           :min="priceRange.min"
                           :max="priceRange.max"
                           placeholder="{{ __('Min') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <span class="text-gray-500">-</span>
                    <input type="number"
                           x-model="filters.priceMax"
                           :min="priceRange.min"
                           :max="priceRange.max"
                           placeholder="{{ __('Max') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- Price Range Slider --}}
                <div class="relative">
                    <input type="range"
                           x-model="filters.priceMax"
                           :min="priceRange.min"
                           :max="priceRange.max"
                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider">
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>{{ __('€') }}{{ $priceRange['min'] }}</span>
                        <span>{{ __('€') }}{{ $priceRange['max'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Categories --}}
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Categories') }}</h4>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @foreach ($categories as $category)
                    <label
                           class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                        <input type="checkbox"
                               x-model="filters.categories"
                               value="{{ $category->id }}"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700">{{ $category->name }}</span>
                        <span class="text-xs text-gray-500 ml-auto">({{ $category->products_count ?? 0 }})</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Brands --}}
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Brands') }}</h4>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @foreach ($brands as $brand)
                    <label
                           class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                        <input type="checkbox"
                               x-model="filters.brands"
                               value="{{ $brand->id }}"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700">{{ $brand->name }}</span>
                        <span class="text-xs text-gray-500 ml-auto">({{ $brand->products_count ?? 0 }})</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Rating --}}
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Rating') }}</h4>
            <div class="space-y-2">
                @for ($i = 5; $i >= 1; $i--)
                    <label
                           class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                        <input type="radio"
                               x-model="filters.rating"
                               value="{{ $i }}"
                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <div class="flex items-center gap-1">
                            @for ($j = 1; $j <= 5; $j++)
                                <svg class="w-4 h-4 {{ $j <= $i ? 'text-yellow-400' : 'text-gray-300' }}"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                            <span class="text-sm text-gray-700 ml-2">{{ $i }}+ {{ __('stars') }}</span>
                        </div>
                    </label>
                @endfor
            </div>
        </div>

        {{-- Availability --}}
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Availability') }}</h4>
            <div class="space-y-2">
                <label
                       class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                    <input type="checkbox"
                           x-model="filters.inStock"
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">{{ __('In Stock') }}</span>
                </label>
                <label
                       class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                    <input type="checkbox"
                           x-model="filters.onSale"
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">{{ __('On Sale') }}</span>
                </label>
                <label
                       class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors duration-200">
                    <input type="checkbox"
                           x-model="filters.newArrivals"
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">{{ __('New Arrivals') }}</span>
                </label>
            </div>
        </div>

        {{-- Apply Filters Button --}}
        <div class="pt-4 border-t border-gray-200">
            <button type="submit"
                    class="w-full btn-gradient py-3 rounded-xl font-semibold">
                {{ __('Apply Filters') }}
            </button>
        </div>
    </form>
</div>


<script>
    function productFilters() {
        return {
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

            init() {
                // Load filters from URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                this.filters.priceMin = urlParams.get('price_min') || '';
                this.filters.priceMax = urlParams.get('price_max') || '';
                this.filters.categories = urlParams.get('categories') ? urlParams.get('categories').split(',') : [];
                this.filters.brands = urlParams.get('brands') ? urlParams.get('brands').split(',') : [];
                this.filters.rating = urlParams.get('rating') || '';
                this.filters.inStock = urlParams.get('in_stock') === '1';
                this.filters.onSale = urlParams.get('on_sale') === '1';
                this.filters.newArrivals = urlParams.get('new_arrivals') === '1';
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

                // Update URL and trigger page reload
                const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.location.href = newUrl;
            },

            clearAllFilters() {
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

                // Clear URL parameters
                window.location.href = window.location.pathname;
            }
        }
    }
</script>

