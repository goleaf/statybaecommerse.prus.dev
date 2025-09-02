@props(['product'])

<div class="group relative">
    <x-product.thumbnail :product="$product" />

    <div class="mt-4 flex justify-between">
        <div>
            <h3 class="text-sm font-medium text-gray-700">
                <x-link :href="route('products.show', [
                    'locale' => app()->getLocale(),
                    'slug' => $product->trans('slug') ?? $product->slug,
                ])">
                    <span aria-hidden="true" class="absolute inset-0"></span>
                    {{ $product->trans('name') ?? $product->name }}
                </x-link>
            </h3>

            @if ($product->brand_id)
                <p class="mt-1 text-sm text-gray-500">
                    @if ($product->brand)
                        <x-link :href="route('brands.show', [
                            'locale' => app()->getLocale(),
                            'slug' => $product->brand->trans('slug') ?? $product->brand->slug,
                        ])">
                            {{ $product->brand?->trans('name') ?? $product->brand?->name }}
                        </x-link>
                    @else
                        {{ $product->brand?->trans('name') ?? $product->brand?->name }}
                    @endif
                </p>
            @endif
            <p class="mt-1 text-xs text-gray-500">
                {{ __('Reserved') }}: {{ $product->reservedQuantity() }}
                <span class="ml-2">{{ __('Available') }}: {{ $product->availableQuantity() }}</span>
                @if ($product->isOutOfStock())
                    <span class="ml-2 text-red-600 font-medium">{{ __('Out of stock') }}</span>
                @endif
            </p>
        </div>
        <x-product.price :product="$product" />
    </div>

    @if ($product->variants_count > 0)
        <p class="mt-3 text-sm text-gray-500">
            {{ __('+:count variants', ['count' => $product->variants_count]) }}
        </p>
    @endif
</div>
