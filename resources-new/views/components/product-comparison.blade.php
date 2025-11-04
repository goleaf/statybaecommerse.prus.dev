@props([
    'products' => null,
    'title' => null,
    'subtitle' => null,
    'maxProducts' => 4,
    'showAddToCart' => true,
    'showWishlist' => true,
])

@php
    $title = $title ?? __('Product Comparison');
    $subtitle = $subtitle ?? __('Compare products side by side to make the best choice');
    $products = $products ?? collect([]);

    // Ensure we don't exceed max products
if ($products->count() > $maxProducts) {
    $products = $products->take($maxProducts);
}

// Define comparison attributes
$comparisonAttributes = [
    'price' => ['label' => __('Price'), 'type' => 'currency'],
    'brand' => ['label' => __('Brand'), 'type' => 'text'],
    'category' => ['label' => __('Category'), 'type' => 'text'],
    'rating' => ['label' => __('Rating'), 'type' => 'rating'],
    'reviews_count' => ['label' => __('Reviews'), 'type' => 'number'],
    'stock_quantity' => ['label' => __('Stock'), 'type' => 'number'],
    'weight' => ['label' => __('Weight'), 'type' => 'text'],
    'dimensions' => ['label' => __('Dimensions'), 'type' => 'text'],
    'warranty' => ['label' => __('Warranty'), 'type' => 'text'],
    'shipping' => ['label' => __('Shipping'), 'type' => 'text'],
    ];
@endphp

<div class="product-comparison" x-data="productComparison()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $subtitle }}</p>
        </div>

        @if ($products->count() > 0)
            {{-- Comparison Table --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-soft">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        {{-- Product Headers --}}
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 w-64">
                                    {{ __('Features') }}
                                </th>
                                @foreach ($products as $product)
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900 min-w-64">
                                        <div class="flex flex-col items-center">
                                            {{-- Product Image --}}
                                            <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden mb-3">
                                                <img src="{{ $product->getFirstMediaUrl('images') ?? asset('images/placeholder-product.jpg') }}"
                                                     alt="{{ $product->name }}"
                                                     class="w-full h-full object-cover">
                                            </div>

                                            {{-- Product Name --}}
                                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                                <a href="{{ route('product.show', $product->slug ?? $product) }}"
                                                   class="hover:text-blue-600 transition-colors duration-200">
                                                    {{ $product->name }}
                                                </a>
                                            </h3>

                                            {{-- Remove Button --}}
                                            <button @click="removeProduct({{ $product->id }})"
                                                    class="text-red-600 hover:text-red-700 text-sm font-medium mb-3">
                                                {{ __('Remove') }}
                                            </button>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            {{-- Price Row --}}
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ __('Price') }}
                                </td>
                                @foreach ($products as $product)
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-lg font-bold text-gray-900">
                                            {{ \Illuminate\Support\Number::currency($product->price, current_currency(), app()->getLocale()) }}
                                        </div>
                                        @if ($product->sale_price && $product->sale_price < $product->price)
                                            <div class="text-sm text-red-600 line-through">
                                                {{ \Illuminate\Support\Number::currency($product->sale_price, current_currency(), app()->getLocale()) }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Brand Row --}}
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ __('Brand') }}
                                </td>
                                @foreach ($products as $product)
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm text-gray-700">
                                            {{ $product->brand->name ?? __('N/A') }}
                                        </span>
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Category Row --}}
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ __('Category') }}
                                </td>
                                @foreach ($products as $product)
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm text-gray-700">
                                            {{ $product->categories->first()->name ?? __('N/A') }}
                                        </span>
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Rating Row --}}
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ __('Rating') }}
                                </td>
                                @foreach ($products as $product)
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1 mb-1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= ($product->avg_rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            {{ $product->reviews_count ?? 0 }} {{ __('reviews') }}
                                        </div>
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Stock Row --}}
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ __('Availability') }}
                                </td>
                                @foreach ($products as $product)
                                    <td class="px-6 py-4 text-center">
                                        @if ($product->stock_quantity > 0)
                                            <span
                                                  class="inline-flex items-center gap-1 text-sm text-green-600 font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                {{ __('In Stock') }}
                                            </span>
                                        @else
                                            <span
                                                  class="inline-flex items-center gap-1 text-sm text-red-600 font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                {{ __('Out of Stock') }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Description Row --}}
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ __('Description') }}
                                </td>
                                @foreach ($products as $product)
                                    <td class="px-6 py-4 text-center">
                                        <p class="text-sm text-gray-700 line-clamp-3">
                                            {{ Str::limit($product->description, 100) }}
                                        </p>
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Actions Row --}}
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ __('Actions') }}
                                </td>
                                @foreach ($products as $product)
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col gap-2">
                                            @if ($showAddToCart)
                                                <button wire:click="addToCart({{ $product->id }})"
                                                        @if ($product->stock_quantity <= 0) disabled @endif
                                                        class="w-full btn-gradient text-sm px-4 py-2 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                                    {{ __('Add to Cart') }}
                                                </button>
                                            @endif

                                            @if ($showWishlist)
                                                <button wire:click="toggleWishlist({{ $product->id }})"
                                                        wire:confirm="{{ __('translations.confirm_toggle_wishlist') }}"
                                                        class="w-full border border-gray-300 text-gray-700 text-sm px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors duration-200">
                                                    {{ __('Add to Wishlist') }}
                                                </button>
                                            @endif

                                            <a href="{{ route('product.show', $product->slug ?? $product) }}"
                                               class="w-full text-blue-600 hover:text-blue-700 text-sm font-medium">
                                                {{ __('View Details') }}
                                            </a>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Summary Section --}}
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Best Value --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Best Value') }}</h3>
                    <div class="text-center">
                        @php
                            $bestValue = $products->sortBy('price')->first();
                        @endphp
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-1">{{ $bestValue->name }}</h4>
                        <p class="text-2xl font-bold text-green-600">
                            {{ \Illuminate\Support\Number::currency($bestValue->price, current_currency(), app()->getLocale()) }}
                        </p>
                    </div>
                </div>

                {{-- Highest Rated --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Highest Rated') }}</h3>
                    <div class="text-center">
                        @php
                            $highestRated = $products->sortByDesc('avg_rating')->first();
                        @endphp
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-1">{{ $highestRated->name }}</h4>
                        <div class="flex items-center justify-center gap-1 mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= ($highestRated->avg_rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <p class="text-sm text-gray-600">{{ $highestRated->reviews_count ?? 0 }} {{ __('reviews') }}
                        </p>
                    </div>
                </div>

                {{-- Most Popular --}}
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Most Popular') }}</h3>
                    <div class="text-center">
                        @php
                            $mostPopular = $products->sortByDesc('reviews_count')->first();
                        @endphp
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-1">{{ $mostPopular->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $mostPopular->reviews_count ?? 0 }} {{ __('reviews') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Clear Comparison --}}
            <div class="mt-8 text-center">
                <button @click="clearComparison()"
                        class="text-red-600 hover:text-red-700 font-medium">
                    {{ __('Clear All Products') }}
                </button>
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-16">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ __('No products to compare') }}</h3>
                <p class="text-gray-600 mb-8">{{ __('Add products to compare their features and specifications') }}
                </p>
                <a href="{{ route('products.index', ['locale' => app()->getLocale()]) ?? '/products' }}"
                   class="btn-gradient px-8 py-3 rounded-xl font-semibold">
                    {{ __('Browse Products') }}
                </a>
            </div>
        @endif
    </div>
</div>

<script>
    function productComparison() {
        return {
            removeProduct(productId) {
                // Remove product from comparison
                const url = new URL(window.location);
                url.searchParams.delete('compare[]');

                // Re-add remaining products
                const currentProducts = {{ $products->pluck('id')->toJson() }};
                const remainingProducts = currentProducts.filter(id => id !== productId);

                remainingProducts.forEach(id => {
                    url.searchParams.append('compare[]', id);
                });

                window.location.href = url.toString();
            },

            clearComparison() {
                if (confirm('{{ __('Are you sure you want to clear all products from comparison?') }}')) {
                    const url = new URL(window.location);
                    url.searchParams.delete('compare[]');
                    window.location.href = url.toString();
                }
            }
        }
    }
</script>
