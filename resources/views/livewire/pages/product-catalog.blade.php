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
        
        {{-- Enhanced Filters --}}
        <x-shared.product-filters 
            :categories="$categories"
            :brands="$brands"
            :showSearch="true"
            :showCategory="true"
            :showBrand="true"
            :showSort="true"
            :showPriceRange="false"
        />
        
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
            emptyStateActionUrl="{{ route('categories.index') }}"
        />
    </x-container>
</div>