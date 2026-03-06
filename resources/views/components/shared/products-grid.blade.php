@props([
    'products',
    'title' => null,
    'description' => null,
    'showQuickAdd' => true,
    'showCompare' => true,
    'showPagination' => true,
    'columns' => 'lg:grid-cols-4', // sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4
    'emptyStateTitle' => null,
    'emptyStateDescription' => null,
    'emptyStateAction' => null,
    'emptyStateActionUrl' => null,
])

@if($products->count() > 0)
    <x-shared.section 
        :title="$title"
        :description="$description"
        icon="heroicon-o-squares-2x2"
        iconColor="text-green-600"
    >
        @if($title && $products instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <x-slot name="title">
                {{ $title }} ({{ $products->total() }} {{ __('messages.shared') }})
            </x-slot>
        @endif
        
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ $columns }} gap-6 mb-8">
            @foreach($products as $product)
                <x-shared.product-card 
                    :product="$product" 
                    :showQuickAdd="$showQuickAdd" 
                    :showCompare="$showCompare" 
                />
            @endforeach
        </div>
        
        @if($showPagination && $products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
            <div class="mt-8 flex justify-center">
                <x-shared.pagination :paginator="$products" />
            </div>
        @endif
    </x-shared.section>
@else
    {{-- Empty State --}}
    <x-shared.empty-state 
        :title="$emptyStateTitle ?? __('messages.shared')"
        :description="$emptyStateDescription ?? __('frontend.products_grid.empty_description')"
        icon="heroicon-o-cube"
        :actionText="$emptyStateAction ?? __('messages.shared')"
        :actionUrl="$emptyStateActionUrl ?? route('frontend.categories.index', [])"
    />
@endif


