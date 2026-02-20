<div class="container mx-auto px-4 py-8">
    <!-- Search Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
            @if($query)
                {{ __('frontend.search_results.results_for') }}: "{{ $query }}"
            @else
                {{ __('frontend.filters.products_title') }}
            @endif
        </h1>
        
        <!-- Results Count -->
        <p class="text-gray-600">
            {{ __('frontend.pagination.showing') }} {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}
            {{ __('frontend.pagination.of') }} {{ $products->total() }} {{ __('frontend.pagination.results') }}
            @if($activeFiltersCount > 0)
                <span class="ml-2">
                    ({{ $activeFiltersCount }} {{ __('frontend.search_results.filters.apply') }})
                </span>
            @endif
        </p>
    </div>

    <div class="lg:grid lg:grid-cols-4 lg:gap-8">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('frontend.search_results.filters.apply') }}</h3>
                    @if($activeFiltersCount > 0)
                        <button 
                            wire:click="clearFilters"
                            wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
                            class="text-sm text-blue-600 hover:text-blue-800"
                        >
                            {{ __('frontend.search_results.filters.clear') }}
                        </button>
                    @endif
                </div>

                <!-- Price Range -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">{{ __('frontend.filters.price_range') }}</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <input 
                            type="number" 
                            wire:model.live.debounce.500ms="minPrice"
                            placeholder="{{ __('frontend.search_results.filters.min') }}"
                            min="{{ $priceRange['min'] }}"
                            max="{{ $priceRange['max'] }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        >
                        <input 
                            type="number" 
                            wire:model.live.debounce.500ms="maxPrice"
                            placeholder="{{ __('frontend.search_results.filters.max') }}"
                            min="{{ $priceRange['min'] }}"
                            max="{{ $priceRange['max'] }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        >
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        €{{ number_format($priceRange['min'], 2) }} - €{{ number_format($priceRange['max'], 2) }}
                    </div>
                </div>

                <!-- Categories -->
                @if($categories->count() > 0)
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">{{ __('frontend.search_results.filters.categories') }}</h4>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($categories as $category)
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:click="toggleCategory({{ $category->id }})"
                                        wire:confirm="{{ __('translations.confirm_toggle_category') }}"
                                        @if(in_array($category->id, $selectedCategories)) checked @endif
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">
                                        {{ $category->name }} ({{ $category->products_count }})
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Brands -->
                @if($brands->count() > 0)
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">{{ __('frontend.search_results.filters.brands') }}</h4>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($brands as $brand)
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:click="toggleBrand({{ $brand->id }})"
                                        wire:confirm="{{ __('translations.confirm_toggle_brand') }}"
                                        @if(in_array($brand->id, $selectedBrands)) checked @endif
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">
                                        {{ $brand->name }} ({{ $brand->products_count }})
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Attributes -->
                @foreach($attributes as $attribute)
                    @if($attribute->values->count() > 0)
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-900 mb-3">{{ $attribute->name }}</h4>
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                @foreach($attribute->values as $value)
                                    <label class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            wire:click="toggleAttribute({{ $value->id }})"
                                            wire:confirm="{{ __('translations.confirm_toggle_attribute') }}"
                                            @if(in_array($value->id, $selectedAttributes)) checked @endif
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">{{ $value->value }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                <!-- Additional Filters -->
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model.live="inStock"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">{{ __('messages.in_stock_only') }}</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model.live="onSale"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">{{ __('frontend.filters.sort.on_sale') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="lg:col-span-3">
            <!-- Toolbar -->
            <div class="flex items-center justify-between mb-6 bg-white rounded-lg shadow-sm border p-4">
                <div class="flex items-center space-x-4">
                    <!-- Per Page -->
                    <select wire:model.live="perPage" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="12">12 {{ __('frontend.pagination.per_page') }}</option>
                        <option value="24">24 {{ __('frontend.pagination.per_page') }}</option>
                        <option value="48">48 {{ __('frontend.pagination.per_page') }}</option>
                    </select>
                </div>

                <!-- Sort Options -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-600">{{ __('frontend.search_results.sort_by') }}:</label>
                    <select wire:model.live="sortBy" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="relevance">{{ __('frontend.search_results.sort.relevance') }}</option>
                        <option value="name">{{ __('messages.name') }}</option>
                        <option value="price_asc">{{ __('frontend.filters.sort.price_low_high') }}</option>
                        <option value="price_desc">{{ __('frontend.filters.sort.price_high_low') }}</option>
                        <option value="created_at">{{ __('frontend.search_results.sort.newest') }}</option>
                        <option value="rating">{{ __('frontend.search_results.sort.highest_rated') }}</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($products as $product)
                        <livewire:components.product-card :product="$product" :key="'product-' . $product->id" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <!-- No Results -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('frontend.search_results.empty.title') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('frontend.search_results.empty.description') }}</p>
                    @if($activeFiltersCount > 0)
                        <button 
                            wire:click="clearFilters"
                            wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
                            class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        >
                            {{ __('frontend.search_results.filters.clear') }}
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
