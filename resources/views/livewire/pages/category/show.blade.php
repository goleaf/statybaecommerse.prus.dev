<div>
    <x-container class="py-8">
        {{-- Category Header --}}
        <x-shared.section 
            title="{{ $category->name }}"
            description="{{ $category->description ?? '' }}"
            icon="heroicon-o-tag"
            titleSize="text-3xl"
            centered="true"
            class="mb-8"
        >
            {{-- Category Image and Stats --}}
            <div class="flex items-center justify-center gap-6 mt-6">
                @if($category->getFirstMediaUrl('images'))
                    <div class="flex-shrink-0">
                        <img src="{{ $category->getFirstMediaUrl('images', 'thumb') }}"
                             alt="{{ $category->name }}"
                             class="w-20 h-20 object-cover rounded-xl shadow-md">
                    </div>
                @endif
                
                <div class="flex gap-4">
                    <x-shared.badge variant="info" size="lg">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        {{ $products->total() }} {{ __('products') }}
                    </x-shared.badge>
                    
                    @if($category->children->count() > 0)
                        <x-shared.badge variant="secondary" size="lg">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            {{ $category->children->count() }} {{ __('subcategories') }}
                        </x-shared.badge>
                    @endif
                </div>
            </div>
        </x-shared.section>"}, {"old_string": "            <x-filament::section \n                icon=\"heroicon-o-squares-2x2\"\n                icon-color=\"success\"\n            >\n                <x-slot name=\"heading\">\n                    {{ __('products_in_category', ['category' => $category->name]) }}\n                </x-slot>\n                \n                <div class=\"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8\">\n                    @foreach($products as $product)\n                        <x-product-card :product=\"$product\" :show-quick-add=\"true\" :show-wishlist=\"true\" :show-compare=\"true\" />\n                    @endforeach\n                </div>\n                \n                {{-- Enhanced Pagination --}}\n                <div class=\"mt-8 flex justify-center\">\n                    <x-filament::pagination :paginator=\"$products\" />\n                </div>\n            </x-filament::section>", "new_string": "            <x-shared.products-grid \n                :products=\"$products\"\n                title=\"{{ __('products_in_category', ['category' => $category->name]) }}\"\n                :showQuickAdd=\"true\"\n                :showWishlist=\"true\"\n                :showCompare=\"true\"\n                :showPagination=\"true\"\n                columns=\"xl:grid-cols-4\"\n            />"}, {"old_string": "            {{-- Enhanced Empty State --}}\n            <x-filament::section \n                icon=\"heroicon-o-magnifying-glass\"\n                icon-color=\"gray\"\n                class=\"text-center py-16\"\n            >\n                <x-slot name=\"heading\">{{ __('No products in this category') }}</x-slot>\n                <x-slot name=\"description\">{{ __('This category is currently empty. Check back later for new products.') }}</x-slot>\n                \n                <div class=\"mt-8 flex justify-center gap-4\">\n                    <x-filament::button \n                        href=\"{{ route('categories.index') }}\"\n                        color=\"primary\"\n                        icon=\"heroicon-o-squares-2x2\"\n                    >\n                        {{ __('browse_other_categories') }}\n                    </x-filament::button>\n                    \n                    <x-filament::button \n                        href=\"{{ route('products.index') }}\"\n                        color=\"secondary\"\n                        outlined\n                        icon=\"heroicon-o-cube\"\n                    >\n                        {{ __('view_all_products') }}\n                    </x-filament::button>\n                </div>\n            </x-filament::section>", "new_string": "            {{-- Empty State --}}\n            <x-shared.empty-state\n                title=\"{{ __('No products in this category') }}\"\n                description=\"{{ __('This category is currently empty. Check back later for new products.') }}\"\n                icon=\"heroicon-o-magnifying-glass\"\n                action-text=\"{{ __('browse_other_categories') }}\"\n                action-url=\"{{ route('categories.index') }}\"\n            >\n                <div class=\"mt-4\">\n                    <x-shared.button \n                        href=\"{{ route('products.index') }}\"\n                        variant=\"secondary\"\n                        icon=\"heroicon-o-cube\"\n                    >\n                        {{ __('view_all_products') }}\n                    </x-shared.button>\n                </div>\n            </x-shared.empty-state>"}]
        
        {{-- Enhanced Sort and Filter Options --}}
        <x-shared.card class="mb-6">
            <x-slot name="header">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                    </svg>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Sort & Filter') }}</h2>
                </div>
            </x-slot>
            
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <x-shared.badge variant="gray">
                        {{ $products->total() }} {{ __('products_found') }}
                    </x-shared.badge>
                </div>
                
                <x-shared.select 
                    wire:model.live="sortBy"
                    label="{{ __('Sort By') }}"
                    class="min-w-48"
                >
                    <option value="created_at">{{ __('Newest') }}</option>
                    <option value="name">{{ __('Name') }}</option>
                    <option value="price">{{ __('Price: Low to High') }}</option>
                    <option value="popularity">{{ __('Popularity') }}</option>
                    <option value="rating">{{ __('Rating') }}</option>
                </x-shared.select>
            </div>
        </x-shared.card>
        
        {{-- Enhanced Products Grid --}}
        @if($products->count() > 0)
            <x-filament::section 
                icon="heroicon-o-squares-2x2"
                icon-color="success"
            >
                <x-slot name="heading">{{ __('category_products') }}</x-slot>
                
                <div class="enhanced-grid mb-8">
                    @foreach($products as $product)
                        <div class="product-card">
                            <x-product-card :product="$product" :show-quick-add="true" :show-wishlist="true" />
                        </div>
                    @endforeach
                </div>
                
                {{-- Enhanced Pagination --}}
                <div class="flex justify-center mt-8">
                    <x-filament::pagination :paginator="$products" />
                </div>
            </x-filament::section>
        @else
            {{-- Enhanced Empty State --}}
            <x-filament::section 
                icon="heroicon-o-cube"
                icon-color="gray"
                class="text-center py-16"
            >
                <x-slot name="heading">{{ __('No products in this category yet.') }}</x-slot>
                <x-slot name="description">{{ __('Check back later for new products') }}</x-slot>
                
                <div class="mt-8 flex justify-center gap-4">
                    <x-filament::button 
                        href="{{ route('products.index') }}"
                        color="primary"
                        icon="heroicon-o-squares-2x2"
                    >
                        {{ __('Browse All Products') }}
                    </x-filament::button>
                    
                    <x-filament::button 
                        href="{{ route('categories.index') }}"
                        color="secondary"
                        outlined
                        icon="heroicon-o-folder"
                    >
                        {{ __('Other Categories') }}
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif
    </x-container>
</div>