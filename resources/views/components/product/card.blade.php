@props(['product'])

@php
    $productSlug = $product->trans('slug') ?? $product->slug ?? $product;
    $productUrl = \Illuminate\Support\Facades\Route::has('product.show')
        ? route('product.show', $productSlug)
        : (\Illuminate\Support\Facades\Route::has('frontend.products.show')
            ? route('frontend.products.show', ['product' => $productSlug])
            : url('/products/' . $productSlug));

    $brandUrl = null;
    if ($product->brand_id && $product->brand) {
        $brandUrl = \Illuminate\Support\Facades\Route::has('brands.show')
            ? route('brands.show', $product->brand)
            : (\Illuminate\Support\Facades\Route::has('frontend.brands.show')
                ? route('frontend.brands.show', ['brand' => $product->brand])
                : null);
    }
@endphp

<div class="group relative">
    <x-product.thumbnail :product="$product" />

    <div class="mt-4 flex justify-between">
        <div>
            <h3 class="text-sm font-medium text-gray-700">
                <x-link :href="$productUrl">
                    <span aria-hidden="true" class="absolute inset-0"></span>
                    {{ $product->trans('name') ?? $product->name }}
                </x-link>
            </h3>

            @if ($product->brand_id)
                <p class="mt-1 text-sm text-gray-500">
                    @if ($product->brand)
                        @if ($brandUrl)
                            <x-link :href="$brandUrl">
                                {{ $product->brand?->trans('name') ?? $product->brand?->name }}
                            </x-link>
                        @else
                            {{ $product->brand?->trans('name') ?? $product->brand?->name }}
                        @endif
                    @else
                        {{ $product->brand?->trans('name') ?? $product->brand?->name }}
                    @endif
                </p>
            @endif
            <p class="mt-1 text-xs text-gray-500">
                {{ __('messages.reserved') }}: {{ $product->reservedQuantity() }}
                <span class="ml-2">{{ __('messages.available') }}: {{ $product->availableQuantity() }}</span>
                @if ($product->isOutOfStock())
                    <span class="ml-2 text-red-600 font-medium">{{ __('messages.out_of_stock') }}</span>
                @endif
            </p>
        </div>
        <x-product.price :product="$product" />
    </div>

    @if ($product->variants_count > 0)
        <p class="mt-3 text-sm text-gray-500">
            {{ __('messages.count_variants', ['count' => $product->variants_count]) }}
        </p>
    @endif
</div>
