<div class="product-variant-selector bg-white rounded-lg shadow-sm p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $this->getVariantLocalizedName() }}</h2>
        <p class="text-gray-600">{{ $this->getVariantLocalizedDescription() }}</p>
    </div>

    <!-- Variant Badges -->
    @if(!empty($this->getVariantBadges()))
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach($this->getVariantBadges() as $badge)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $badge['type'] === 'new' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $badge['type'] === 'featured' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $badge['type'] === 'bestseller' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $badge['type'] === 'sale' ? 'bg-red-100 text-red-800' : '' }}
                ">
                    {{ $badge['label'] }}
                </span>
            @endforeach
        </div>
    @endif

    <!-- Attribute Selection -->
    @foreach($attributes as $attribute)
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">
                {{ $attribute->getLocalizedName() }}:
                @if($selectedVariant)
                    <span class="text-gray-500 font-normal">
                        {{ $this->getAttributeValueDisplay($attribute->slug, $selectedAttributes[$attribute->slug] ?? '') }}
                    </span>
                @endif
            </label>
            
            <div class="flex flex-wrap gap-2">
                @foreach($attribute->attributeValues as $attributeValue)
                    @php
                        $isAvailable = $this->isAttributeValueAvailable($attribute->slug, $attributeValue->value);
                        $isSelected = ($selectedAttributes[$attribute->slug] ?? '') === $attributeValue->value;
                    @endphp
                    
                    <button
                        wire:click="selectAttribute('{{ $attribute->slug }}', '{{ $attributeValue->value }}')"
                        class="px-4 py-2 border rounded-md text-sm font-medium transition-colors
                            {{ $isSelected ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-700 hover:border-gray-400' }}
                            {{ !$isAvailable ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50' }}"
                        {{ !$isAvailable ? 'disabled' : '' }}
                    >
                        {{ $attributeValue->getLocalizedDisplayValue() }}
                        @if(!$isAvailable)
                            <span class="text-xs text-gray-400 ml-1">({{ __('product_variants.messages.out_of_stock') }})</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Price Display -->
    @if($selectedVariant)
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-3xl font-bold text-gray-900">
                            €{{ number_format($this->getVariantPrice(), 2) }}
                        </span>
                        
                        @if($this->getVariantOriginalPrice())
                            <span class="text-lg text-gray-500 line-through">
                                €{{ number_format($this->getVariantOriginalPrice(), 2) }}
                            </span>
                        @endif
                        
                        @if($this->getVariantDiscountPercentage())
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                -{{ $this->getVariantDiscountPercentage() }}%
                            </span>
                        @endif
                    </div>
                    
                    @if($this->getVariantPromotionalPrice())
                        <p class="text-sm text-green-600 mt-1">
                            {{ __('product_variants.price_types.promotional') }}: €{{ number_format($this->getVariantPromotionalPrice(), 2) }}
                        </p>
                    @endif
                </div>
                
                <div class="text-right">
                    <p class="text-sm text-gray-600">{{ $this->getStockMessage() }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Quantity and Actions -->
    @if($selectedVariant)
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('product_variants.fields.quantity') }}:
            </label>
            
            <div class="flex items-center space-x-4">
                <div class="flex items-center border border-gray-300 rounded-md">
                    <button
                        wire:click="decrementQuantity"
                        class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50"
                        {{ $quantity <= 1 ? 'disabled' : '' }}
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    
                    <span class="px-4 py-2 text-gray-900 font-medium">{{ $quantity }}</span>
                    
                    <button
                        wire:click="incrementQuantity"
                        class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50"
                        {{ $quantity >= $selectedVariant->available_quantity ? 'disabled' : '' }}
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="text-sm text-gray-500">
                    {{ __('product_variants.messages.max_quantity') }}: {{ $selectedVariant->available_quantity }}
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <button
                wire:click="addToCart"
                class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                {{ $selectedVariant->available_quantity < $quantity ? 'disabled' : '' }}
            >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                </svg>
                {{ __('product_variants.actions.add_to_cart') }}
            </button>
            
            <button
                wire:click="addToComparison"
                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-md font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
            >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                {{ __('product_variants.actions.compare') }}
            </button>
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-500">{{ __('product_variants.messages.select_variant') }}</p>
        </div>
    @endif

    <!-- Comparison Section -->
    @if($showComparison && !empty($comparisonVariants))
        <div class="mt-8 border-t pt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">{{ __('product_variants.comparison.title') }}</h3>
                <button
                    wire:click="clearComparison"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    {{ __('product_variants.comparison.clear_all') }}
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($comparisonVariants as $variantId)
                    @php
                        $variant = $variants->firstWhere('id', $variantId);
                    @endphp
                    @if($variant)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900">{{ $variant->getLocalizedName() }}</h4>
                                <button
                                    wire:click="removeFromComparison({{ $variant->id }})"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">{{ $variant->getLocalizedDescription() }}</p>
                            <p class="text-lg font-bold text-gray-900">€{{ number_format($variant->getCurrentPrice(), 2) }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- Error Messages -->
    @error('variant')
        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-600">{{ $message }}</p>
        </div>
    @enderror

    @error('quantity')
        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-600">{{ $message }}</p>
        </div>
    @enderror

    <!-- Success Message -->
    @if(session()->has('success'))
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-600">{{ session('success') }}</p>
        </div>
    @endif
</div>
