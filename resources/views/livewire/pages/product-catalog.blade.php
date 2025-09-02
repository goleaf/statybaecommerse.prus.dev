<div class="min-h-screen bg-gray-50">
    <!-- Header with Search and Filters -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1 max-w-lg">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="{{ __('Search products...') }}"
                        >
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <button 
                        wire:click="toggleFilters"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
                        </svg>
                        {{ __('Filters') }}
                    </button>
                    
                    <select 
                        wire:model.live="sortBy" 
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="name">{{ __('Name') }}</option>
                        <option value="price">{{ __('Price') }}</option>
                        <option value="created_at">{{ __('Newest') }}</option>
                        <option value="stock_quantity">{{ __('Stock') }}</option>
                    </select>
                    
                    <select 
                        wire:model.live="sortDirection" 
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="asc">{{ __('Ascending') }}</option>
                        <option value="desc">{{ __('Descending') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    @if($showFilters)
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Categories Filter -->
                <div>
                    <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('Categories') }}</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($categories as $category)
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                wire:model.live="selectedCategories" 
                                value="{{ $category->id }}"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-600">
                                {{ $category->name }} ({{ $category->products_count }})
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Brands Filter -->
                <div>
                    <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('Brands') }}</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($brands as $brand)
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                wire:model.live="selectedBrands" 
                                value="{{ $brand->id }}"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-600">
                                {{ $brand->name }} ({{ $brand->products_count }})
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Price Range Filter -->
                <div>
                    <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('Price Range') }}</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-500">{{ __('Min Price') }}</label>
                            <input 
                                type="number" 
                                wire:model.live.debounce.500ms="priceMin"
                                min="0"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm"
                            >
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">{{ __('Max Price') }}</label>
                            <input 
                                type="number" 
                                wire:model.live.debounce.500ms="priceMax"
                                min="0"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm"
                            >
                        </div>
                    </div>
                </div>

                <!-- Availability Filter -->
                <div>
                    <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('Availability') }}</h3>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                wire:model.live="availability" 
                                value="all"
                                class="text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-600">{{ __('All Products') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                wire:model.live="availability" 
                                value="in_stock"
                                class="text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-600">{{ __('In Stock') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                wire:model.live="availability" 
                                value="out_of_stock"
                                class="text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-600">{{ __('Out of Stock') }}</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-between items-center">
                <button 
                    wire:click="clearFilters"
                    class="text-sm text-indigo-600 hover:text-indigo-500"
                >
                    {{ __('Clear all filters') }}
                </button>
                
                <div class="text-sm text-gray-500">
                    {{ $products->total() }} {{ __('products found') }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Products Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div class="text-sm text-gray-500">
                {{ __('Showing') }} {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} {{ __('of') }} {{ $products->total() }} {{ __('products') }}
            </div>
            
            <select 
                wire:model.live="perPage" 
                class="block w-auto pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            >
                <option value="12">12 {{ __('per page') }}</option>
                <option value="24">24 {{ __('per page') }}</option>
                <option value="48">48 {{ __('per page') }}</option>
            </select>
        </div>

        @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
            <div class="group relative bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-t-lg bg-gray-200">
                    @if($product->getFirstMediaUrl('images'))
                    <img 
                        src="{{ $product->getFirstMediaUrl('images', 'medium') }}" 
                        alt="{{ $product->name }}"
                        class="h-full w-full object-cover object-center group-hover:opacity-75 transition-opacity"
                        loading="lazy"
                    >
                    @else
                    <div class="flex items-center justify-center h-full bg-gray-200">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    @endif
                </div>
                
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-sm font-medium text-gray-900 line-clamp-2">
                            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-indigo-600">
                                {{ $product->name }}
                            </a>
                        </h3>
                        @if($product->brand)
                        <span class="text-xs text-gray-500">{{ $product->brand->name }}</span>
                        @endif
                    </div>
                    
                    @if($product->description)
                    <p class="text-xs text-gray-600 mb-3 line-clamp-2">{{ Str::limit($product->description, 80) }}</p>
                    @endif
                    
                    <div class="flex justify-between items-center">
                        <div class="flex flex-col">
                            <span class="text-lg font-semibold text-gray-900">€{{ number_format($product->price, 2) }}</span>
                            @if($product->stock_quantity > 0)
                            <span class="text-xs text-green-600">{{ __('In stock') }} ({{ $product->stock_quantity }})</span>
                            @else
                            <span class="text-xs text-red-600">{{ __('Out of stock') }}</span>
                            @endif
                        </div>
                        
                        <button 
                            wire:click="addToCart({{ $product->id }})"
                            @if($product->stock_quantity <= 0) disabled @endif
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7M17 18a2 2 0 11-4 0 2 2 0 014 0zM9 18a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ __('Add') }}
                        </button>
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
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.5-.81-6.172-2.172C5.088 12.088 5 11.544 5 11V5a2 2 0 012-2h10a2 2 0 012 2v6c0 .544-.088 1.088-.828 1.828z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No products found') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('Try adjusting your search or filter criteria.') }}</p>
            <div class="mt-6">
                <button 
                    wire:click="clearFilters"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    {{ __('Clear filters') }}
                </button>
            </div>
        </div>
        @endif
    </div>
</div>