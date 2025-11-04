<div class="product-variant-showcase">
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('product_variants.showcase.title') }}</h1>
        <p class="text-gray-600">{{ __('product_variants.showcase.subtitle') }}</p>
    </div>

    <!-- Product Selection -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('product_variants.showcase.select_product') }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($products as $product)
                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors cursor-pointer
                    {{ $selectedProduct && $selectedProduct->id === $product->id ? 'border-blue-500 bg-blue-50' : '' }}"
                    wire:click="selectProduct({{ $product->id }})"
                >
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-medium text-gray-900">{{ $product->name }}</h3>
                        <span class="text-sm text-gray-500">{{ $product->variants->count() }} {{ __('product_variants.showcase.variants_count') }}</span>
                    </div>
                    
                    @if($product->brand)
                        <p class="text-sm text-gray-600 mb-2">{{ __('product_variants.showcase.brand') }}: {{ $product->brand->name }}</p>
                    @endif
                    
                    <p class="text-sm text-gray-500 line-clamp-2">{{ $product->description }}</p>
                    
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-gray-900">€{{ number_format($product->price, 2) }}</span>
                        @if($product->is_featured)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ __('product_variants.badges.featured') }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if($selectedProduct)
        <!-- Product Stats -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('product_variants.showcase.analytics_title') }}</h2>
            
            @php
                $stats = $this->getProductStats();
            @endphp
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total_variants'] }}</div>
                    <div class="text-sm text-gray-600">{{ __('product_variants.showcase.total_variants') }}</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['in_stock'] }}</div>
                    <div class="text-sm text-gray-600">{{ __('product_variants.showcase.in_stock') }}</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['low_stock'] }}</div>
                    <div class="text-sm text-gray-600">{{ __('product_variants.showcase.low_stock') }}</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['out_of_stock'] }}</div>
                    <div class="text-sm text-gray-600">{{ __('product_variants.showcase.out_of_stock') }}</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $stats['on_sale'] }}</div>
                    <div class="text-sm text-gray-600">{{ __('product_variants.showcase.on_sale') }}</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ $stats['featured'] }}</div>
                    <div class="text-sm text-gray-600">{{ __('product_variants.showcase.featured') }}</div>
                </div>
            </div>
            
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">€{{ number_format($stats['average_price'], 2) }}</div>
                    <div class="text-sm text-gray-600">{{ __('product_variants.showcase.average_price') }}</div>
                </div>
                
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">€{{ number_format($stats['highest_price'], 2) }}</div>
                    <div class="text-sm text-gray-600">{{ __('product_variants.showcase.highest_price') }}</div>
                </div>
                
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">€{{ number_format($stats['lowest_price'], 2) }}</div>
                    <div class="text-sm text-gray-600">{{ __('product_variants.showcase.lowest_price') }}</div>
                </div>
            </div>
        </div>

        <!-- Variant Selection -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('product_variants.showcase.variant_selection') }}</h2>
            
            @if($attributes->isNotEmpty())
                <div class="space-y-4">
                    @foreach($attributes as $attribute)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
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
                </div>
            @endif
        </div>

        <!-- Selected Variant Details -->
        @if($selectedVariant)
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('product_variants.showcase.selected_variant') }}</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $selectedVariant->getLocalizedName() }}</h3>
                        <p class="text-gray-600 mb-4">{{ $selectedVariant->getLocalizedDescription() }}</p>
                        
                        <!-- Variant Badges -->
                        @if(!empty($this->getVariantBadges($selectedVariant)))
                            <div class="mb-4 flex flex-wrap gap-2">
                                @foreach($this->getVariantBadges($selectedVariant) as $badge)
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
                        
                        <!-- Price -->
                        <div class="mb-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-3xl font-bold text-gray-900">
                                    €{{ number_format($this->getVariantPrice($selectedVariant), 2) }}
                                </span>
                                
                                @if($this->getVariantOriginalPrice($selectedVariant))
                                    <span class="text-lg text-gray-500 line-through">
                                        €{{ number_format($this->getVariantOriginalPrice($selectedVariant), 2) }}
                                    </span>
                                @endif
                                
                                @if($this->getVariantDiscountPercentage($selectedVariant))
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        -{{ $this->getVariantDiscountPercentage($selectedVariant) }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Stock Status -->
                        <div class="mb-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $this->getVariantStockStatus($selectedVariant) === 'in_stock' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $this->getVariantStockStatus($selectedVariant) === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $this->getVariantStockStatus($selectedVariant) === 'out_of_stock' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $this->getVariantStockStatus($selectedVariant) === 'not_tracked' ? 'bg-gray-100 text-gray-800' : '' }}
                            ">
                                {{ __('product_variants.stock_status.' . $this->getVariantStockStatus($selectedVariant)) }}
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-2">{{ __('product_variants.showcase.variant_attributes') }}</h4>
                        <div class="space-y-2">
                            @foreach($this->getVariantAttributes($selectedVariant) as $attributeName => $attributeData)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">{{ ucfirst($attributeName) }}:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $attributeData['localized'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            <button
                                wire:click="addToComparison({{ $selectedVariant->id }})"
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                {{ __('product_variants.actions.add_to_comparison') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- All Variants Grid -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('product_variants.showcase.all_variants') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($productVariants as $variant)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors
                        {{ $selectedVariant && $selectedVariant->id === $variant->id ? 'border-blue-500 bg-blue-50' : '' }}"
                        wire:click="selectAttribute('{{ $variant->variantAttributeValues->first()?->attribute_name }}', '{{ $variant->variantAttributeValues->first()?->attribute_value }}')"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-gray-900 text-sm">{{ $variant->getLocalizedName() }}</h4>
                            <button
                                wire:click.stop="addToComparison({{ $variant->id }})"
                                class="text-gray-400 hover:text-blue-500"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="text-sm text-gray-600 mb-2">
                            @foreach($this->getVariantAttributes($variant) as $attributeName => $attributeData)
                                <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                    {{ $attributeData['localized'] }}
                                </span>
                            @endforeach
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-gray-900">€{{ number_format($this->getVariantPrice($variant), 2) }}</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $this->getVariantStockStatus($variant) === 'in_stock' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $this->getVariantStockStatus($variant) === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $this->getVariantStockStatus($variant) === 'out_of_stock' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $this->getVariantStockStatus($variant) === 'not_tracked' ? 'bg-gray-100 text-gray-800' : '' }}
                            ">
                                {{ __('product_variants.stock_status.' . $this->getVariantStockStatus($variant)) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Comparison Section -->
        @if($showComparison && !empty($comparisonVariants))
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('product_variants.comparison.title') }}</h2>
                    <button
                        wire:click="clearComparison"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        {{ __('product_variants.comparison.clear_all') }}
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($comparisonVariants as $variantId)
                        @php
                            $variant = $productVariants->firstWhere('id', $variantId);
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
                                <p class="text-lg font-bold text-gray-900">€{{ number_format($this->getVariantPrice($variant), 2) }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
