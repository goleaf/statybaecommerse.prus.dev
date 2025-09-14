<div class="container mx-auto px-4 py-8">
    <!-- Search Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
            @if($query)
                {{ __('Search Results for') }}: "{{ $query }}"
            @else
                {{ __('All Products') }}
            @endif
        </h1>
        
        <!-- Results Count -->
        <p class="text-gray-600">
            {{ __('Showing') }} {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} 
            {{ __('of') }} {{ $products->total() }} {{ __('results') }}
            @if($activeFiltersCount > 0)
                <span class="ml-2">
                    ({{ $activeFiltersCount }} {{ __('filters applied') }})
                </span>
            @endif
        </p>
    </div>

    <div class="lg:grid lg:grid-cols-4 lg:gap-8">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Filters') }}</h3>
                    @if($activeFiltersCount > 0)
                        <button 
                            wire:click="clearFilters"
                            wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
                            class="text-sm text-blue-600 hover:text-blue-800"
                        >
                            {{ __('Clear All') }}
                        </button>
                    @endif
                </div>

                <!-- Price Range -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">{{ __('Price Range') }}</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <input 
                            type="number" 
                            wire:model.live.debounce.500ms="minPrice"
                            placeholder="{{ __('Min') }}"
                            min="{{ $priceRange['min'] }}"
                            max="{{ $priceRange['max'] }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        >
                        <input 
                            type="number" 
                            wire:model.live.debounce.500ms="maxPrice"
                            placeholder="{{ __('Max') }}"
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
                        <h4 class="font-medium text-gray-900 mb-3">{{ __('Categories') }}</h4>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($categories as $category)
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:click="toggleCategory({{ $category->id }})"
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
                        <h4 class="font-medium text-gray-900 mb-3">{{ __('Brands') }}</h4>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($brands as $brand)
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:click="toggleBrand({{ $brand->id }})"
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
                        <span class="ml-2 text-sm text-gray-700">{{ __('In Stock Only') }}</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model.live="onSale"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">{{ __('On Sale') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="lg:col-span-3">
            <!-- Toolbar -->
            <div class="flex items-center justify-between mb-6 bg-white rounded-lg shadow-sm border p-4">
                <div class="flex items-center space-x-4">
                    <!-- View Mode Toggle -->
                    <div class="flex border border-gray-300 rounded">
                        <button 
                            wire:click="$set('viewMode', 'grid')"
                            class="p-2 {{ $viewMode === 'grid' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                        </button>
                        <button 
                            wire:click="$set('viewMode', 'list')"
                            class="p-2 {{ $viewMode === 'list' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 8a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 12a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 16a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Per Page -->
                    <select wire:model.live="perPage" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="12">12 {{ __('per page') }}</option>
                        <option value="24">24 {{ __('per page') }}</option>
                        <option value="48">48 {{ __('per page') }}</option>
                    </select>
                </div>

                <!-- Sort Options -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-600">{{ __('Sort by') }}:</label>
                    <select wire:model.live="sortBy" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="relevance">{{ __('Relevance') }}</option>
                        <option value="name">{{ __('Name') }}</option>
                        <option value="price_asc">{{ __('Price: Low to High') }}</option>
                        <option value="price_desc">{{ __('Price: High to Low') }}</option>
                        <option value="created_at">{{ __('Newest') }}</option>
                        <option value="rating">{{ __('Best Rated') }}</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid/List -->
            @if($products->count() > 0)
                <div class="{{ $viewMode === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6' : 'space-y-4' }}">
                    @foreach($products as $product)
                        @if($viewMode === 'grid')
                            <livewire:components.product-card :product="$product" :key="'product-' . $product->id" />
                        @else
                            <!-- List View -->
                            <div class="bg-white rounded-lg shadow-sm border p-4 flex space-x-4">
                                <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    @if($product->getFirstMediaUrl('images'))
                                        <img 
                                            src="{{ $product->getFirstMediaUrl('images', 'image-sm') ?: $product->getFirstMediaUrl('images') }}" 
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-cover"
                                        >
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 mb-1">
                                        <a href="{{ route('product.show', $product->slug ?? $product) }}" class="hover:text-blue-600">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                    <p class="text-gray-600 text-sm mb-2">{{ Str::limit($product->description, 100) }}</p>
                                    <div class="flex items-center justify-between">
                                        <div class="text-lg font-bold text-gray-900">
                                            €{{ number_format($product->variants->first()?->price ?? 0, 2) }}
                                        </div>
                                        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                                            {{ __('Add to Cart') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
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
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No products found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Try adjusting your search or filter criteria') }}</p>
                    @if($activeFiltersCount > 0)
                        <button 
                            wire:click="clearFilters"
                            wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
                            class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        >
                            {{ __('Clear Filters') }}
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
