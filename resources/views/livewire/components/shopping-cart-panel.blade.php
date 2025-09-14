<div>
    <!-- Cart Toggle Button -->
    <button 
        wire:click="toggleCart"
        class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors duration-200"
        aria-label="{{ __('translations.shopping_cart') }}"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5-6M20 13v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2-2v4m16 0H4" />
        </svg>
        
        @if($cartSummary['items_count'] > 0)
            <span class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                {{ $cartSummary['items_count'] }}
            </span>
        @endif
    </button>

    <!-- Cart Sliding Panel -->
    <div 
        x-data="{ open: @entangle('isOpen') }"
        x-show="open"
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-gray-800 shadow-xl"
        style="display: none;"
    >
        <!-- Cart Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('translations.shopping_cart') }}
                @if($cartSummary['items_count'] > 0)
                    ({{ $cartSummary['items_count'] }})
                @endif
            </h2>
            <button 
                wire:click="toggleCart"
                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Cart Content -->
        <div class="flex flex-col h-full">
            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4">
                @if($cartItems->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5-6M20 13v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2-2v4m16 0H4" />
                        </svg>
                        <p class="mt-4 text-gray-500 dark:text-gray-400">{{ __('translations.cart_is_empty') }}</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('translations.add_products_to_cart') }}</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($cartItems as $item)
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <!-- Product Image -->
                                <div class="flex-shrink-0">
                                    <img 
                                        src="{{ $item->product->getFirstMediaUrl('images', 'thumb') ?: asset('images/placeholder-product.png') }}"
                                        alt="{{ $item->product->name }}"
                                        class="w-12 h-12 object-cover rounded-md"
                                    >
                                </div>

                                <!-- Product Details -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $item->product->name }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $item->product->brand?->name }}
                                    </p>
                                    <p class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                        {{ app_money_format($item->price) }}
                                    </p>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center space-x-2">
                                    <button 
                                        wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                        @if($item->quantity <= 1) disabled @endif
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    
                                    <span class="text-sm font-medium text-gray-900 dark:text-white min-w-[2rem] text-center">
                                        {{ $item->quantity }}
                                    </span>
                                    
                                    <button 
                                        wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                    
                                    <button 
                                        wire:click="removeItem({{ $item->id }})"
                                        wire:confirm="{{ __('translations.confirm_remove_cart_item') }}"
                                        class="p-1 text-red-400 hover:text-red-600"
                                        title="{{ __('translations.remove_item') }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Discount Code Section -->
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">
                            {{ __('translations.discount_code') }}
                        </h4>
                        
                        @if($appliedDiscount)
                            <div class="flex items-center justify-between p-2 bg-green-100 dark:bg-green-900 rounded-md">
                                <span class="text-sm text-green-800 dark:text-green-200">
                                    {{ $appliedDiscount->code }} (-{{ app_money_format($cartSummary['discount_amount']) }})
                                </span>
                                <button 
                                    wire:click="removeDiscountCode"
                                    wire:confirm="{{ __('translations.confirm_remove_discount_code') }}"
                                    class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @else
                            <div class="flex space-x-2">
                                <input 
                                    wire:model="discountCode"
                                    type="text" 
                                    placeholder="{{ __('translations.enter_discount_code') }}"
                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                >
                                <button 
                                    wire:click="applyDiscountCode"
                                    class="px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors duration-200"
                                >
                                    {{ __('translations.apply') }}
                                </button>
                            </div>
                            @error('discountCode')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                @endif
            </div>

            <!-- Cart Footer -->
            @if(!$cartItems->isEmpty())
                <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700">
                    <!-- Cart Summary -->
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('translations.subtotal') }}</span>
                            <span class="text-gray-900 dark:text-white">{{ app_money_format($cartSummary['subtotal']) }}</span>
                        </div>
                        
                        @if($cartSummary['discount_amount'] > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600 dark:text-green-400">{{ __('translations.discount') }}</span>
                                <span class="text-green-600 dark:text-green-400">-{{ app_money_format($cartSummary['discount_amount']) }}</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('translations.tax') }}</span>
                            <span class="text-gray-900 dark:text-white">{{ app_money_format($cartSummary['tax_amount']) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('translations.shipping') }}</span>
                            <span class="text-gray-900 dark:text-white">
                                @if($cartSummary['shipping_amount'] > 0)
                                    {{ app_money_format($cartSummary['shipping_amount']) }}
                                @else
                                    {{ __('translations.free') }}
                                @endif
                            </span>
                        </div>
                        
                        <div class="flex justify-between text-base font-semibold border-t border-gray-200 dark:border-gray-600 pt-2">
                            <span class="text-gray-900 dark:text-white">{{ __('translations.total') }}</span>
                            <span class="text-blue-600 dark:text-blue-400">{{ app_money_format($cartSummary['total']) }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2">
                        <button 
                            wire:click="proceedToCheckout"
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium"
                        >
                            {{ __('translations.proceed_to_checkout') }}
                        </button>
                        
                        <div class="flex space-x-2">
                            <a 
                                href="{{ route('cart.index', app()->getLocale()) }}"
                                class="flex-1 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 py-2 px-4 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors duration-200 text-center text-sm"
                            >
                                {{ __('translations.view_cart') }}
                            </a>
                            
                            <button 
                                wire:click="clearCart"
                                wire:confirm="{{ __('translations.confirm_clear_cart') }}"
                                class="flex-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 py-2 px-4 rounded-md hover:bg-red-200 dark:hover:bg-red-800 transition-colors duration-200 text-sm"
                            >
                                {{ __('translations.clear_cart') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Backdrop -->
    <div 
        x-data="{ open: @entangle('isOpen') }"
        x-show="open"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="$wire.toggleCart()"
        class="fixed inset-0 bg-black bg-opacity-50 z-40"
        style="display: none;"
    ></div>
</div>
