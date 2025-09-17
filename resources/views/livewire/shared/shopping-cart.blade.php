<div class="relative">
    {{-- Cart Icon Button --}}
    <button wire:click="toggleCart" 
            wire:confirm="{{ __('translations.confirm_toggle_cart') }}"
            class="flex items-center rounded-full bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white transition-all duration-200">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5M7 13l-1.1 5m0 0h9.1M16 13v8a2 2 0 01-2 2H8a2 2 0 01-2-2v-8m8 0V9a2 2 0 00-2-2H8a2 2 0 00-2 2v4.01"/>
        </svg>
        @if ($this->cartCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                {{ $this->cartCount }}
            </span>
        @endif
    </button>

    {{-- Cart Dropdown --}}
    @if ($isOpen)
        <div class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
            <div class="p-4">
                <h3 class="text-lg font-semibold mb-4">{{ __('ecommerce.shopping_cart') }}</h3>
                
                @if ($this->cartItems->count() > 0)
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach ($this->cartItems as $item)
                            <div class="flex items-center space-x-3 border-b border-gray-100 pb-3">
                                @if ($item->product->getFirstMedia())
                                    <img src="{{ $item->product->getFirstMediaUrl() }}" 
                                         alt="{{ $item->product->name }}" 
                                         class="w-12 h-12 object-cover rounded">
                                @endif
                                
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium">{{ $item->product->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ format_currency($item->price) }}</p>
                                    
                                    <div class="flex items-center space-x-2 mt-1">
                                        <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                                class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                        </button>
                                        <span class="text-sm">{{ $item->quantity }}</span>
                                        <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                                class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </button>
                                        <button wire:click="removeItem({{ $item->id }})"
                                                wire:confirm="{{ __('translations.confirm_remove_cart_item') }}"
                                                class="text-red-400 hover:text-red-600 ml-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center mb-3">
                            <span class="font-semibold">{{ __('store.cart.total') }}:</span>
                            <span class="font-bold text-lg">{{ format_currency($this->cartTotal) }}</span>
                        </div>
                        
                        <div class="space-y-2">
                            <a href="{{ route('cart.index') }}" 
                               class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 transition-colors">
                                {{ __('store.nav.cart') }}
                            </a>
                            <a href="{{ route('checkout.index') }}" 
                               class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                {{ __('store.checkout.proceed') }}
                            </a>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">{{ __('ecommerce.cart_empty') }}</p>
                @endif
            </div>
        </div>
    @endif
</div>
