<div class="product-variant-selector">
    @if($attributes->isNotEmpty())
        <div class="variant-attributes mb-6">
            @foreach($attributes as $attribute)
                <div class="attribute-group mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $attribute->name }}
                    </label>
                    
                    <div class="attribute-values flex flex-wrap gap-2">
                        @foreach($this->getAvailableValues($attribute->slug) as $value)
                            <button
                                type="button"
                                wire:click="onAttributeChange('{{ $attribute->slug }}', '{{ $value->value }}')"
                                class="attribute-value-btn px-4 py-2 border rounded-lg text-sm font-medium transition-colors duration-200
                                    {{ $this->isValueSelected($attribute->slug, $value->value) 
                                        ? 'bg-blue-600 text-white border-blue-600' 
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-blue-500 hover:text-blue-600' }}
                                    {{ !$this->isValueAvailable($attribute->slug, $value->value) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ !$this->isValueAvailable($attribute->slug, $value->value) ? 'disabled' : '' }}
                            >
                                {{ $value->display_value ?? $value->value }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($selectedVariant)
        <div class="variant-details bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ $selectedVariant->name }}
                </h3>
                <span class="text-sm text-gray-500">
                    SKU: {{ $selectedVariant->variant_sku }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="text-sm text-gray-600">{{ __('product_variants.fields.price') }}:</span>
                    <span class="text-xl font-bold text-gray-900 ml-2">
                        €{{ number_format($this->getVariantPrice(), 2) }}
                    </span>
                    @if($selectedVariant->compare_price && $selectedVariant->compare_price > $selectedVariant->price)
                        <span class="text-sm text-gray-500 line-through ml-2">
                            €{{ number_format($selectedVariant->compare_price, 2) }}
                        </span>
                    @endif
                </div>

                <div>
                    <span class="text-sm text-gray-600">{{ __('product_variants.fields.stock_status') }}:</span>
                    <span class="ml-2 text-sm font-medium
                        {{ $this->getVariantStockStatus() === 'in_stock' ? 'text-green-600' : '' }}
                        {{ $this->getVariantStockStatus() === 'low_stock' ? 'text-yellow-600' : '' }}
                        {{ $this->getVariantStockStatus() === 'out_of_stock' ? 'text-red-600' : '' }}
                    ">
                        {{ $this->getVariantStockMessage() }}
                    </span>
                </div>
            </div>

            @if($selectedVariant->size_display_name)
                <div class="mb-4">
                    <span class="text-sm text-gray-600">{{ __('product_variants.fields.size') }}:</span>
                    <span class="ml-2 font-medium">{{ $selectedVariant->size_display_name }}</span>
                </div>
            @endif

            @if($selectedVariant->weight)
                <div class="mb-4">
                    <span class="text-sm text-gray-600">{{ __('product_variants.fields.weight') }}:</span>
                    <span class="ml-2 font-medium">{{ number_format($selectedVariant->getFinalWeight(), 2) }} kg</span>
                </div>
            @endif
        </div>

        <div class="add-to-cart-section">
            <div class="quantity-selector flex items-center gap-4 mb-4">
                <label class="text-sm font-medium text-gray-700">
                    {{ __('product_variants.fields.quantity') }}:
                </label>
                
                <div class="flex items-center border border-gray-300 rounded-lg">
                    <button
                        type="button"
                        wire:click="decrementQuantity"
                        class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-colors"
                        {{ $quantity <= 1 ? 'disabled' : '' }}
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    
                    <input
                        type="number"
                        wire:model="quantity"
                        min="1"
                        max="{{ $selectedVariant->availableQuantity() }}"
                        class="w-16 px-3 py-2 text-center border-0 focus:ring-0 focus:outline-none"
                    >
                    
                    <button
                        type="button"
                        wire:click="incrementQuantity"
                        class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-colors"
                        {{ $quantity >= $selectedVariant->availableQuantity() ? 'disabled' : '' }}
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <button
                type="button"
                wire:click="addToCart"
                class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200
                    {{ !$selectedVariant->isAvailableForPurchase() ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ !$selectedVariant->isAvailableForPurchase() ? 'disabled' : '' }}
            >
                @if($selectedVariant->isAvailableForPurchase())
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                    </svg>
                    {{ __('product_variants.actions.add_to_cart') }}
                @else
                    {{ __('product_variants.messages.not_available') }}
                @endif
            </button>
        </div>
    @else
        <div class="no-variant-selected text-center py-8">
            <div class="text-gray-500 mb-4">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <p class="text-gray-600">{{ __('product_variants.messages.select_variant') }}</p>
        </div>
    @endif

    @if($selectedVariant && $selectedVariant->images->isNotEmpty())
        <div class="variant-images mt-6">
            <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('product_variants.fields.images') }}</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach($selectedVariant->images->take(4) as $image)
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                        <img
                            src="{{ $image->image_url }}"
                            alt="{{ $image->formatted_alt_text }}"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-200"
                        >
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('show-success', (event) => {
            // Show success notification
            console.log('Success:', event.message);
        });

        Livewire.on('show-error', (event) => {
            // Show error notification
            console.log('Error:', event.message);
        });

        Livewire.on('add-to-cart', (event) => {
            // Handle add to cart
            console.log('Add to cart:', event);
        });
    });
</script>
@endpush
