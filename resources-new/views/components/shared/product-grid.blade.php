@props([
    'products',
    'columns' => 4, // 2, 3, 4, 5, 6
    'layout' => 'grid', // grid, list
    'showQuickAdd' => true,
    'showWishlist' => true,
    'showCompare' => true,
    'showPagination' => true,
    'emptyTitle' => null,
    'emptyDescription' => null,
    'emptyActionText' => null,
    'emptyActionUrl' => null,
])

@php
$gridClasses = match($columns) {
    2 => 'grid-cols-1 sm:grid-cols-2',
    3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
    5 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
    6 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6',
    default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
};

// Use splitIn method for better product distribution if available
$organizedProducts = $products;
if (method_exists($products, 'splitIn') && $products->count() > $columns) {
    $organizedProducts = $products->splitIn($columns);
}
@endphp

@if($products->count() > 0)
    <div class="space-y-8">
        {{-- Products Grid --}}
        @if($layout === 'grid' && isset($organizedProducts) && $organizedProducts instanceof \Illuminate\Support\Collection && $organizedProducts->first() instanceof \Illuminate\Support\Collection)
            {{-- Use splitIn organized layout --}}
            <div class="grid gap-6 {{ $gridClasses }}">
                @foreach($organizedProducts as $columnIndex => $columnProducts)
                    <div class="space-y-6">
                        @foreach($columnProducts as $product)
                            <x-shared.product-card 
                                :product="$product"
                                :layout="$layout"
                                :show-quick-add="$showQuickAdd"
                                :show-wishlist="$showWishlist"
                                :show-compare="$showCompare"
                            />
                        @endforeach
                    </div>
                @endforeach
            </div>
        @else
            {{-- Standard grid layout --}}
            <div @class([
                'grid gap-6',
                $gridClasses => $layout === 'grid',
                'space-y-4' => $layout === 'list',
            ])>
                @foreach($products as $product)
                    <x-shared.product-card 
                        :product="$product"
                        :layout="$layout"
                        :show-quick-add="$showQuickAdd"
                        :show-wishlist="$showWishlist"
                        :show-compare="$showCompare"
                    />
                @endforeach
            </div>
        @endif

        {{-- Pagination --}}
        @if($showPagination && method_exists($products, 'hasPages') && $products->hasPages())
            <div class="mt-12">
                <x-shared.pagination :paginator="$products" />
            </div>
        @endif
    </div>
@else
    {{-- Empty State --}}
    <x-shared.empty-state
        :title="$emptyTitle ?? __('shared.no_results_found')"
        :description="$emptyDescription ?? __('shared.cart_empty_description')"
        icon="heroicon-o-cube"
        :action-text="$emptyActionText"
        :action-url="$emptyActionUrl"
    />
@endif
