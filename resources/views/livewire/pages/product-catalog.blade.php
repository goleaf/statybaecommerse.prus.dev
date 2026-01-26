<div>
    <x-container class="py-8">
        {{-- Page Header --}}
        <x-shared.section 
            title="{{ __('messages.products') }}"
            description="{{ __('messages.product_catalog_description') }}"
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
                title="{{ __('messages.products_found', ['count' => $products->total()]) }}"
                :showQuickAdd="true"
                :showWishlist="true"
                :showCompare="true"
                :showPagination="true"
                columns="xl:grid-cols-4"
                emptyStateTitle="{{ __('messages.no_products_found') }}"
                emptyStateDescription="{{ __('messages.try_adjusting_your_search_or_filter_criteria') }}"
                emptyStateAction="{{ __('messages.browse_categories') }}"
                emptyStateActionUrl="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
            />
        </x-shared.sidebar-layout>
    </x-container>
</div>
