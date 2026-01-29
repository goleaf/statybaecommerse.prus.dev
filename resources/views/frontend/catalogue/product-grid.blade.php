@props([
    'products',
    'columns' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
    'emptyMessage' => __('frontend.catalogue.grid.empty_default'),
])

<div class="grid gap-6 {{ $columns }}">
    @forelse($products as $product)
        <x-product-card
            :product="$product"
            :showQuickView="false"
            :showWishlist="false"
            :showCompare="false"
            :showAddToCart="false"
        />
    @empty
        <div class="col-span-full rounded-2xl border border-dashed border-gray-200 bg-white p-10 text-center text-sm text-gray-500">
            {{ $emptyMessage }}
        </div>
    @endforelse
</div>

@if($products instanceof \Illuminate\Contracts\Pagination\Paginator)
    <div class="mt-10">
        {{ $products->links('pagination::tailwind') }}
    </div>
@endif
