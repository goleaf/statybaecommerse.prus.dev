<div>
    <x-container class="py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Products') }}</h1>
        
        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
                    <input type="text" 
                           id="search"
                           wire:model.live.debounce.300ms="search" 
                           placeholder="{{ __('Search products...') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Category') }}</label>
                    <select id="category" wire:model.live="categoryId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Brand Filter -->
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Brand') }}</label>
                    <select id="brand" wire:model.live="brandId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">{{ __('All Brands') }}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Sort -->
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Sort By') }}</label>
                    <select id="sort" wire:model.live="sortBy" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="created_at">{{ __('Newest') }}</option>
                        <option value="name">{{ __('Name') }}</option>
                        <option value="price">{{ __('Price') }}</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                @foreach($products as $product)
                    <x-product.card :product="$product" />
                @endforeach
            </div>
            
            <!-- Pagination -->
            {{ $products->links() }}
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">🔍</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No products found') }}</h3>
                <p class="text-gray-500">{{ __('Try adjusting your search or filter criteria') }}</p>
            </div>
        @endif
    </x-container>
</div>