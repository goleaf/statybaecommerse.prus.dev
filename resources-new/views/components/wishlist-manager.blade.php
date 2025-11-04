@props([
    'wishlistItems' => null,
    'title' => null,
    'subtitle' => null,
    'showImages' => true,
    'showPrices' => true,
    'showStock' => true,
    'showAddToCart' => true,
    'showRemove' => true,
    'maxItems' => null,
])

@php
    $title = $title ?? __('My Wishlist');
    $subtitle = $subtitle ?? __('Save your favorite products for later');
    $wishlistItems = $wishlistItems ?? collect([]);

    if ($maxItems) {
        $wishlistItems = $wishlistItems->take($maxItems);
    }
@endphp

<div class="wishlist-manager" x-data="wishlistManager()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">{{ $title }}</h1>
                <p class="text-lg text-gray-600">{{ $subtitle }}</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">
                    {{ $wishlistItems->count() }} {{ __('items') }}
                </span>

                @if ($wishlistItems->count() > 0)
                    <button @click="clearWishlist()"
                            class="text-red-600 hover:text-red-700 font-medium text-sm">
                        {{ __('Clear All') }}
                    </button>
                @endif
            </div>
        </div>

        @if ($wishlistItems->count() > 0)
            {{-- Wishlist Items --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($wishlistItems as $item)
                    <div
                         class="bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-large transition-shadow duration-300 group">
                        {{-- Product Image --}}
                        @if ($showImages)
                            <div class="aspect-w-1 aspect-h-1 bg-gray-100 relative overflow-hidden">
                                <img src="{{ $item->getFirstMediaUrl('images') ?? asset('images/placeholder-product.jpg') }}"
                                     alt="{{ $item->name }}"
                                     class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300">

                                {{-- Remove from Wishlist Button --}}
                                @if ($showRemove)
                                    <button wire:click="removeFromWishlist({{ $item->id }})"
                                            wire:confirm="{{ __('translations.confirm_remove_wishlist_item') }}"
                                            class="absolute top-3 right-3 w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-red-600 hover:bg-white hover:scale-110 transition-all duration-200 shadow-soft">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                @endif

                                {{-- Stock Badge --}}
                                @if ($showStock)
                                    @if ($item->stock_quantity > 0)
                                        <div class="absolute top-3 left-3">
                                            <span
                                                  class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                                {{ __('In Stock') }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="absolute top-3 left-3">
                                            <span
                                                  class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                                {{ __('Out of Stock') }}
                                            </span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif

                        {{-- Product Details --}}
                        <div class="p-6">
                            {{-- Product Name --}}
                            <h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-2">
                                <a href="{{ route('product.show', $item->slug ?? $item) }}"
                                   class="hover:text-blue-600 transition-colors duration-200">
                                    {{ $item->name }}
                                </a>
                            </h3>

                            {{-- Brand --}}
                            @if ($item->brand)
                                <p class="text-sm text-gray-600 mb-2">{{ $item->brand->name }}</p>
                            @endif

                            {{-- Rating --}}
                            @if ($item->avg_rating > 0)
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="flex items-center">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $item->avg_rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                                 fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-sm text-gray-600">({{ $item->reviews_count ?? 0 }})</span>
                                </div>
                            @endif

                            {{-- Price --}}
                            @if ($showPrices)
                                <div class="mb-4">
                                    @if ($item->sale_price && $item->sale_price < $item->price)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl font-bold text-gray-900">
                                                {{ \Illuminate\Support\Number::currency($item->sale_price, current_currency(), app()->getLocale()) }}
                                            </span>
                                            <span class="text-lg text-gray-500 line-through">
                                                {{ \Illuminate\Support\Number::currency($item->price, current_currency(), app()->getLocale()) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xl font-bold text-gray-900">
                                            {{ \Illuminate\Support\Number::currency($item->price, current_currency(), app()->getLocale()) }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="space-y-2">
                                @if ($showAddToCart)
                                    <button wire:click="addToCart({{ $item->id }})"
                                            @if ($item->stock_quantity <= 0) disabled @endif
                                            class="w-full btn-gradient text-sm px-4 py-2 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                        {{ __('Add to Cart') }}
                                    </button>
                                @endif

                                <a href="{{ route('product.show', $item->slug ?? $item) }}"
                                   class="w-full border border-gray-300 text-gray-700 text-sm px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors duration-200 text-center block">
                                    {{ __('View Details') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Bulk Actions --}}
            <div class="mt-8 bg-white border border-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Bulk Actions') }}</h3>
                <div class="flex flex-col sm:flex-row gap-4">
                    <button @click="addAllToCart()"
                            class="btn-gradient px-6 py-3 rounded-lg font-medium">
                        {{ __('Add All to Cart') }}
                    </button>
                    <button @click="shareWishlist()"
                            class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors duration-200">
                        {{ __('Share Wishlist') }}
                    </button>
                    <button @click="exportWishlist()"
                            class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors duration-200">
                        {{ __('Export List') }}
                    </button>
                </div>
            </div>

            {{-- Recommendations --}}
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('You might also like') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Add recommendation products here --}}
                    @for ($i = 0; $i < 4; $i++)
                        <div
                             class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-medium transition-shadow duration-200">
                            <div class="w-full h-48 bg-gray-100 rounded-lg mb-4"></div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ __('Recommended Product') }}</h3>
                            <p class="text-gray-600 text-sm mb-3">{{ __('Product description here') }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-gray-900">€99.99</span>
                                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                    {{ __('Add to Wishlist') }}
                                </button>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-16">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                    </path>
                </svg>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Your wishlist is empty') }}</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    {{ __('Start adding products to your wishlist by clicking the heart icon on any product page.') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('localized.products.index', ['locale' => app()->getLocale()]) }}"
                       class="btn-gradient px-8 py-3 rounded-xl font-semibold">
                        {{ __('Browse Products') }}
                    </a>
                    <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
                       class="border-2 border-gray-300 text-gray-700 px-8 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                        {{ __('View Categories') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function wishlistManager() {
        return {
            clearWishlist() {
                if (confirm('{{ __('Are you sure you want to clear your entire wishlist?') }}')) {
                    // Clear wishlist logic
                    window.location.href = '/wishlist/clear';
                }
            },

            addAllToCart() {
                // Add all wishlist items to cart
                const productIds = {{ $wishlistItems->pluck('id')->toJson() }};

                productIds.forEach(productId => {
                    // Add to cart logic
                    fetch('/cart/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: 1
                        })
                    });
                });

                this.showNotification('{{ __('All items added to cart!') }}', 'success');
            },

            shareWishlist() {
                // Share wishlist functionality
                if (navigator.share) {
                    navigator.share({
                        title: '{{ __('My Wishlist') }}',
                        text: '{{ __('Check out my wishlist!') }}',
                        url: window.location.href
                    });
                } else {
                    // Fallback: copy to clipboard
                    navigator.clipboard.writeText(window.location.href);
                    this.showNotification('{{ __('Wishlist link copied to clipboard!') }}', 'success');
                }
            },

            exportWishlist() {
                // Export wishlist as CSV or PDF
                const wishlistData = {{ $wishlistItems->toJson() }};

                // Create CSV content
                let csvContent = "Product Name,Brand,Price,Rating\n";
                wishlistData.forEach(item => {
                    csvContent +=
                        `"${item.name}","${item.brand?.name || 'N/A'}","${item.price}","${item.avg_rating || 0}"\n`;
                });

                // Download CSV
                const blob = new Blob([csvContent], {
                    type: 'text/csv'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'wishlist.csv';
                a.click();
                window.URL.revokeObjectURL(url);

                this.showNotification('{{ __('Wishlist exported successfully!') }}', 'success');
            },

            showNotification(message, type) {
                // Create and show notification
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-large ${
                type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'
            }`;
                notification.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 ${type === 'success' ? 'text-green-600' : 'text-red-600'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>${message}</span>
                </div>
            `;

                document.body.appendChild(notification);

                // Remove notification after 3 seconds
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        }
    }
</script>
