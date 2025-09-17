@props([
    'cart' => null,
    'showImages' => true,
    'showQuantityControls' => true,
    'showRemoveButton' => true,
    'showSubtotal' => true,
    'showTax' => true,
    'showShipping' => true,
    'showTotal' => true,
])

@php
    $cart = $cart ?? session('cart', []);
    $cartItems = $cart['items'] ?? [];
    $subtotal = $cart['subtotal'] ?? 0;
    $tax = $cart['tax'] ?? 0;
    $shipping = $cart['shipping'] ?? 0;
    $total = $cart['total'] ?? 0;
@endphp

<div class="shopping-cart" x-data="shoppingCart()">
    @if (count($cartItems) > 0)
        {{-- Cart Items --}}
        <div class="space-y-4">
            @foreach ($cartItems as $item)
                <div
                     class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-medium transition-shadow duration-200">
                    <div class="flex items-center gap-4">
                        {{-- Product Image --}}
                        @if ($showImages)
                            <div class="flex-shrink-0">
                                <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden">
                                    <img src="{{ $item['image'] ?? asset('images/placeholder-product.jpg') }}"
                                         alt="{{ $item['name'] }}"
                                         class="w-full h-full object-cover">
                                </div>
                            </div>
                        @endif

                        {{-- Product Details --}}
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 truncate">
                                <a href="{{ $item['url'] ?? '#' }}"
                                   class="hover:text-blue-600 transition-colors duration-200">
                                    {{ $item['name'] }}
                                </a>
                            </h3>

                            @if (isset($item['attributes']) && count($item['attributes']) > 0)
                                <div class="mt-1 text-sm text-gray-600">
                                    @foreach ($item['attributes'] as $key => $value)
                                        <span class="inline-block mr-3">
                                            <span class="font-medium">{{ $key }}:</span> {{ $value }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-2 flex items-center gap-4">
                                {{-- Price --}}
                                <span class="text-lg font-bold text-gray-900">
                                    {{ \Illuminate\Support\Number::currency($item['price'], current_currency(), app()->getLocale()) }}
                                </span>

                                {{-- Stock Status --}}
                                @if (isset($item['stock_quantity']))
                                    @if ($item['stock_quantity'] > 0)
                                        <span class="inline-flex items-center gap-1 text-sm text-green-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ __('In Stock') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-sm text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            {{ __('Out of Stock') }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Quantity Controls --}}
                        @if ($showQuantityControls)
                            <div class="flex items-center gap-2">
                                <button @click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                        class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                                        :disabled="{{ $item['quantity'] }} <= 1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M20 12H4"></path>
                                    </svg>
                                </button>

                                <span class="w-12 text-center font-medium">{{ $item['quantity'] }}</span>

                                <button @click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                        class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                                        :disabled="{{ $item['stock_quantity'] ?? 999 }} <= {{ $item['quantity'] }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif

                        {{-- Item Total --}}
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900">
                                {{ \Illuminate\Support\Number::currency($item['price'] * $item['quantity'], current_currency(), app()->getLocale()) }}
                            </div>
                        </div>

                        {{-- Remove Button --}}
                        @if ($showRemoveButton)
                            <button @click="removeItem({{ $item['id'] }})"
                                    class="flex-shrink-0 p-2 text-gray-400 hover:text-red-600 transition-colors duration-200"
                                    title="{{ __('Remove from cart') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Cart Summary --}}
        <div class="mt-8 bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Order Summary') }}</h3>

            <div class="space-y-3">
                {{-- Subtotal --}}
                @if ($showSubtotal)
                    <div class="flex justify-between text-gray-700">
                        <span>{{ __('Subtotal') }}</span>
                        <span>{{ \Illuminate\Support\Number::currency($subtotal, current_currency(), app()->getLocale()) }}</span>
                    </div>
                @endif

                {{-- Tax --}}
                @if ($showTax && $tax > 0)
                    <div class="flex justify-between text-gray-700">
                        <span>{{ __('Tax') }}</span>
                        <span>{{ \Illuminate\Support\Number::currency($tax, current_currency(), app()->getLocale()) }}</span>
                    </div>
                @endif

                {{-- Shipping --}}
                @if ($showShipping)
                    <div class="flex justify-between text-gray-700">
                        <span>{{ __('Shipping') }}</span>
                        <span>
                            @if ($shipping > 0)
                                {{ \Illuminate\Support\Number::currency($shipping, current_currency(), app()->getLocale()) }}
                            @else
                                {{ __('Free') }}
                            @endif
                        </span>
                    </div>
                @endif

                {{-- Divider --}}
                <div class="border-t border-gray-200"></div>

                {{-- Total --}}
                @if ($showTotal)
                    <div class="flex justify-between text-lg font-bold text-gray-900">
                        <span>{{ __('Total') }}</span>
                        <span>{{ \Illuminate\Support\Number::currency($total, current_currency(), app()->getLocale()) }}</span>
                    </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="mt-6 space-y-3">
                <a href="{{ localized_route('checkout.index') }}"
                   class="w-full btn-gradient py-3 rounded-xl font-semibold text-center block">
                    {{ __('Proceed to Checkout') }}
                </a>

                <a href="{{ localized_route('products.index') }}"
                   class="w-full border-2 border-gray-300 text-gray-700 py-3 rounded-xl font-semibold text-center block hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                    {{ __('Continue Shopping') }}
                </a>
            </div>
        </div>
    @else
        {{-- Empty Cart --}}
        <div class="text-center py-12">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01">
                </path>
            </svg>

            <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Your cart is empty') }}</h3>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                {{ __('Looks like you haven\'t added any items to your cart yet. Start shopping to fill it up!') }}
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ localized_route('products.index') }}"
                   class="btn-gradient px-8 py-3 rounded-xl font-semibold">
                    {{ __('Start Shopping') }}
                </a>

                <a href="{{ localized_route('categories.index') }}"
                   class="border-2 border-gray-300 text-gray-700 px-8 py-3 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-colors duration-200">
                    {{ __('Browse Categories') }}
                </a>
            </div>
        </div>
    @endif
</div>

<script>
    function shoppingCart() {
        return {
            loading: false,

            async updateQuantity(productId, newQuantity) {
                if (newQuantity < 1) {
                    this.removeItem(productId);
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch('/cart/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: newQuantity
                        })
                    });

                    if (response.ok) {
                        // Reload page to update cart
                        window.location.reload();
                    } else {
                        this.showNotification('{{ __('Failed to update quantity. Please try again.') }}', 'error');
                    }
                } catch (error) {
                    this.showNotification('{{ __('Network error. Please check your connection and try again.') }}',
                        'error');
                } finally {
                    this.loading = false;
                }
            },

            async removeItem(productId) {
                if (!confirm('{{ __('Are you sure you want to remove this item from your cart?') }}')) {
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch('/cart/remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            product_id: productId
                        })
                    });

                    if (response.ok) {
                        // Reload page to update cart
                        window.location.reload();
                    } else {
                        this.showNotification('{{ __('Failed to remove item. Please try again.') }}', 'error');
                    }
                } catch (error) {
                    this.showNotification('{{ __('Network error. Please check your connection and try again.') }}',
                        'error');
                } finally {
                    this.loading = false;
                }
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"></path>
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

