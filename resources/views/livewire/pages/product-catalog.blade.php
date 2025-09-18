<div>
    <x-container class="py-8">
        {{-- Page Header --}}
        <x-shared.section 
            title="{{ __('Products') }}"
            description="{{ __('product_catalog_description') }}"
            icon="heroicon-o-cube"
            titleSize="text-3xl"
            centered="true"
        />
        
        {{-- Sidebar Layout with Filters --}}
        <x-shared.sidebar-layout 
            sidebarWidth="w-80"
            contentWidth="flex-1"
            sidebarSticky="true"
            sidebarClass="lg:pr-6"
            contentClass="lg:pl-6"
        >
            <x-slot name="sidebar">
                <x-shared.sidebar-filters 
                    :categories="$categories"
                    :brands="$brands"
                    :showSearch="true"
                    :showCategory="true"
                    :showBrand="true"
                    :showSort="true"
                    :showPriceRange="true"
                    :showClearFilters="true"
                    :showApplyFilters="true"
                />
            </x-slot>
            
            {{-- Products Grid --}}
            <x-shared.products-grid 
                :products="$products"
                title="{{ __('products_found', ['count' => $products->total()]) }}"
                :showQuickAdd="true"
                :showWishlist="true"
                :showCompare="true"
                :showPagination="true"
                columns="xl:grid-cols-4"
                emptyStateTitle="{{ __('No products found') }}"
                emptyStateDescription="{{ __('Try adjusting your search or filter criteria') }}"
                emptyStateAction="{{ __('browse_categories') }}"
                emptyStateActionUrl="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
            />
        </x-shared.sidebar-layout>
    </x-container>
</div>
