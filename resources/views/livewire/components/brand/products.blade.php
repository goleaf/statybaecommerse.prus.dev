<div wire:loading.attr="aria-busy" aria-busy="false">
    <div wire:loading class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-6" role="status" aria-live="polite">
        @for ($i = 0; $i < 8; $i++)
            <x-skeleton.product-card />
        @endfor
    </div>
    @if ($products->isEmpty())
        <div class="text-slate-500" aria-live="polite">{{ __('No products for this brand yet.') }}</div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($products as $product)
                <a href="{{ route('product.show', $product->trans('slug') ?? $product->slug) }}"
                   class="block border rounded-lg p-4 hover:shadow-sm">
                    <x-product.thumbnail :product="$product" containerClass="mb-3" />
                    <div class="text-base font-medium">{{ $product->trans('name') ?? $product->name }}</div>
                    <x-product.price :product="$product" class="mt-1" />
                </a>
            @endforeach
        </div>

        <nav class="mt-6" aria-label="{{ __('Pagination') }}">{{ $products->links() }}</nav>
    @endif
</div>
