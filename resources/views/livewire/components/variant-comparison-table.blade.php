<div class="variant-comparison-table bg-white shadow-sm rounded-lg p-6">
    @if($showComparison && $variantsToCompare->isNotEmpty())
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">{{ __('product.variants.comparison.title') }}</h2>
                <button
                    wire:click="clearComparison"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    {{ __('product.variants.comparison.clear_all') }}
                </button>
            </div>
            <p class="text-gray-600 mt-2">{{ __('product.variants.comparison.subtitle', ['count' => $variantsToCompare->count()]) }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('product.variants.comparison.variant') }}
                        </th>
                        @foreach($variantsToCompare as $variant)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider relative">
                                <button
                                    wire:click="removeVariantFromComparison({{ $variant->id }})"
                                    class="absolute top-1 right-1 text-red-500 hover:text-red-700"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <div class="pr-6">
                                    <div class="font-medium text-gray-900">{{ $variant->getLocalizedName() }}</div>
                                    <div class="text-xs text-gray-500">{{ $variant->product->name }}</div>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Price Row -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ __('product.variants.fields.price') }}
                        </td>
                        @foreach($variantsToCompare as $variant)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex flex-col">
                                    <span class="font-bold text-lg">€{{ number_format($this->getVariantPrice($variant), 2) }}</span>
                                    @if($this->getVariantOriginalPrice($variant))
                                        <span class="text-xs text-gray-400 line-through">
                                            €{{ number_format($this->getVariantOriginalPrice($variant), 2) }}
                                        </span>
                                    @endif
                                    @if($this->getVariantDiscountPercentage($variant))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                            -{{ $this->getVariantDiscountPercentage($variant) }}%
                                        </span>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>

                    <!-- Stock Status Row -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ __('product.variants.fields.stock_status') }}
                        </td>
                        @foreach($variantsToCompare as $variant)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $this->getVariantStockStatus($variant) === 'in_stock' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $this->getVariantStockStatus($variant) === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $this->getVariantStockStatus($variant) === 'out_of_stock' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $this->getVariantStockStatus($variant) === 'not_tracked' ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ __(sprintf('product.variants.stock_status.%s', $this->getVariantStockStatus($variant))) }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $this->getVariantStockMessage($variant) }}
                                </div>
                            </td>
                        @endforeach
                    </tr>

                    <!-- Weight Row -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ __('product.variants.fields.weight') }}
                        </td>
                        @foreach($variantsToCompare as $variant)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($this->getVariantWeight($variant), 2) }} kg
                            </td>
                        @endforeach
                    </tr>

                    <!-- Views and Clicks Row -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ __('product.variants.fields.views_count') }}
                        </td>
                        @foreach($variantsToCompare as $variant)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($variant->views_count) }}
                            </td>
                        @endforeach
                    </tr>

                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ __('product.variants.fields.clicks_count') }}
                        </td>
                        @foreach($variantsToCompare as $variant)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($variant->clicks_count) }}
                            </td>
                        @endforeach
                    </tr>

                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ __('product.variants.fields.conversion_rate') }}
                        </td>
                        @foreach($variantsToCompare as $variant)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($variant->conversion_rate, 2) }}%
                            </td>
                        @endforeach
                    </tr>

                    <!-- Badges Row -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ __('product.variants.fields.badges') }}
                        </td>
                        @foreach($variantsToCompare as $variant)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($this->getVariantBadges($variant) as $badge)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            {{ $badge['type'] === 'new' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $badge['type'] === 'featured' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $badge['type'] === 'bestseller' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $badge['type'] === 'sale' ? 'bg-red-100 text-red-800' : '' }}
                                        ">
                                            {{ $badge['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        @endforeach
                    </tr>

                    <!-- Dynamic Attribute Rows -->
                    @foreach($this->getAllAttributeNames() as $attributeName)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ ucfirst($attributeName) }}
                            </td>
                            @foreach($variantsToCompare as $variant)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $attributes = $this->getVariantAttributes($variant);
                                        $attributeValue = $attributes[$attributeName] ?? null;
                                    @endphp
                                    {{ $attributeValue ? $attributeValue['localized'] : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    <!-- Actions Row -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ __('product.variants.actions.actions') }}
                        </td>
                        @foreach($variantsToCompare as $variant)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex flex-col space-y-2">
                                    <button
                                        class="inline-flex items-center cursor-pointer px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        onclick="addToCart({{ $variant->id }})"
                                    >
                                        {{ __('product.variants.actions.add_to_cart') }}
                                    </button>
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        onclick="viewVariant({{ $variant->id }})"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        {{ __('product.variants.actions.view_details') }}
                                    </button>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('product.variants.comparison.no_variants_selected') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('product.variants.comparison.select_variants_to_compare') }}</p>
        </div>
    @endif
</div>

<script>
function addToCart(variantId) {
    // Add to cart functionality
    console.log('Adding variant to cart:', variantId);
}

function viewVariant(variantId) {
    // View variant details functionality
    console.log('Viewing variant:', variantId);
}
</script>
