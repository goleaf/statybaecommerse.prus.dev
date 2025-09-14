<div>
    <div class="space-y-4">
        <h1 class="font-heading text-xl font-semibold text-gray-900 lg:text-2xl">
            {{ $product->name }}

            @if ($this->variant)
                {{ $this->variant->name }}
            @endif
        </h1>

        <x-product.price
                         :product="$this->variant ?? $product"
                         class="text-lg font-bold text-brand lg:text-2xl" />

        <div class="text-sm text-gray-600">
            @if ($product->isVariant())
                @if ($this->variant)
                    <span>{{ __('frontend.products.reserved') }}: {{ $this->variant->reservedQuantity() }}</span>
                    <span class="ml-3">{{ __('frontend.products.available') }}: {{ $this->variant->availableQuantity() }}</span>
                @endif
            @else
                <span>{{ __('frontend.products.reserved') }}: {{ $product->reservedQuantity() }}</span>
                <span class="ml-3">{{ __('frontend.products.available') }}: {{ $product->availableQuantity() }}</span>
            @endif
        </div>
    </div>

    <form class="mt-6 space-y-10" wire:submit="addToCart">
        @if ($product->isVariant() && $this->productOptions->isNotEmpty())
            <div class="space-y-5">
                @foreach ($this->productOptions as $option)
                    @if (\Illuminate\Support\Facades\View::exists('components.attributes.' . $option->attribute->slug))
                        <x-dynamic-component
                                             :component="'attributes.' . $option->attribute->slug"
                                             :option="$option" />
                    @else
                        <div>
                            <div class="text-sm font-medium mb-2">
                                {{ $option->attribute->trans('name') ?? $option->attribute->name }}
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($option->values as $val)
                                    <label class="inline-flex items-center gap-1 text-sm">
                                        <input type="radio" name="option_{{ $option->attribute->id }}"
                                               value="{{ $val->id }}"
                                               wire:model.live="selectedOptionValues.{{ $option->attribute->id }}" />
                                        <span>{{ $val->trans('value') ?? $val->value }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        @if ($product->isVariant() && $this->productOptions->isEmpty())
            <div class="grid grid-cols-3 gap-3">
                @foreach ($this->variants as $variant)
                    <button
                            type="button"
                            wire:key="{{ $variant->id }}"
                            x-on:click="$wire.set('selectedVariantId', {{ $variant->id }})"
                            @class([
                                'inline-flex flex-col items-center gap-1 overflow-hidden text-sm/5 text-gray-500 px-2 py-1.5 ring-1 ring-gray-100 hover:ring-gray-200',
                                'ring-2 ring-primary-600' => $variant->id === $selectedVariantId,
                            ])>
                        @php($thumb = $variant->getFirstMediaUrl(config('media.storage.thumbnail_collection')) ?: ($variant->getFirstMediaUrl(config('media.storage.collection_name'), 'small') ?: ($variant->getFirstMediaUrl(config('media.storage.collection_name'), 'medium') ?: $variant->getFirstMediaUrl(config('media.storage.collection_name')))))
                        @if ($thumb)
                            <img
                                 src="{{ $thumb }}"
                                 alt="{{ $variant->name }}"
                                 class="object-center object-cover max-w-none h-14 w-full">
                        @endif
                        <span>{{ $variant->name }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        @if ($product->shouldHideAddToCart())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-400 mr-2" />
                    <p class="text-yellow-800 text-sm">
                        {{ __('frontend.product.cannot_add_to_cart_message') }}
                    </p>
                </div>
            </div>
        @else
            <x-buttons.primary
                               type="submit"
                               class="w-full px-8 py-3 text-base"
                               wire:loading.attr="disabled"
                               wire:target="addToCart"
                               :disabled="($product->isVariant() && !$this->variant) ||
                                   ($product->isVariant() && $this->variant && $this->variant->stock < 1) ||
                                   (!$product->isVariant() && $product->stock < 1)">
                <span class="absolute left-0 top-0 pl-2">
                    <x-phosphor-shopping-bag-duotone class="size-6 text-white" aria-hidden="true" wire:loading.remove />
                    <x-loading-dots class="bg-white hidden" aria-hidden="true" wire:loading.class="block" />
                </span>
                @if ($product->isVariant())
                    @if (!$this->variant)
                        {{ __('Select options') }}
                    @elseif($this->variant && $this->variant->isOutOfStock())
                        {{ __('Out of stock') }}
                    @else
                        {{ __('frontend.products.add_to_cart') }}
                    @endif
                @else
                    @if ($product->isOutOfStock())
                        {{ __('Out of stock') }}
                    @else
                        {{ __('frontend.products.add_to_cart') }}
                    @endif
                @endif
            </x-buttons.primary>
        @endif
    </form>

    <!-- Product Request Form -->
    @if ($product->is_requestable)
        <div class="mt-6">
            <livewire:components.product-request-form :product="$product" />
        </div>
    @endif
</div>
