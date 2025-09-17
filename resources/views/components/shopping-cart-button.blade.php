@props(['count' => 0])

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open"
            class="relative p-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-lg transition-colors duration-200"
            aria-label="{{ __('Shopping Cart') }} ({{ $count }} {{ __('items') }})">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01">
            </path>
        </svg>

        @if ($count > 0)
            <span
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold animate-bounce">
                {{ $count > 99 ? '99+' : $count }}
            </span>
        @endif
    </button>

    {{-- Cart Dropdown --}}
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-large border border-gray-200 z-50"
         style="display: none;">

        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Shopping Cart') }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            @if ($count > 0)
                {{-- Cart Items --}}
                <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                    {{-- Sample cart item - replace with actual cart items --}}
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <img src="{{ asset('images/placeholder-product.jpg') }}"
                             alt="Product"
                             class="w-12 h-12 object-cover rounded-lg">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">Sample Product</h4>
                            <p class="text-sm text-gray-600">€29.99 × 2</p>
                        </div>
                        <button class="text-red-500 hover:text-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Cart Summary --}}
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-lg font-semibold text-gray-900">{{ __('Total') }}:</span>
                        <span class="text-lg font-bold text-blue-600">€59.98</span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ localized_route('cart.index') }}"
                           class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-center font-medium hover:bg-gray-200 transition-colors duration-200">
                            {{ __('View Cart') }}
                        </a>
                        <a href="{{ localized_route('checkout.index') }}"
                           class="flex-1 btn-gradient px-4 py-2 rounded-lg text-center font-medium">
                            {{ __('Checkout') }}
                        </a>
                    </div>
                </div>
            @else
                {{-- Empty Cart --}}
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01">
                        </path>
                    </svg>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">{{ __('Your cart is empty') }}</h4>
                    <p class="text-gray-600 mb-4">{{ __('Add some products to get started') }}</p>
                    <a href="{{ localized_route('products.index') }}"
                       class="btn-gradient px-6 py-2 rounded-lg font-medium">
                        {{ __('Start Shopping') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

