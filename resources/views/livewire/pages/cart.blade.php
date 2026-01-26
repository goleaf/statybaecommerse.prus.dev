<div class="cart-page">
    <!-- Hero Section -->
    <section class="relative bg-sage z-10 overflow-hidden">
        <x-container class="px-4 py-16">
            <div class="max-w-[1320px] mx-auto w-full space-y-6 text-dark text-center">
                <p class="uppercase text-3xl md:text-4xl font-medium">
                    {{ __('messages.your_cart') }}
                </p>
                <p class="text-sm max-w-2xl mx-auto">
                    {{ __('messages.review_your_selected_items_and_proceed_to_checkout') }}
                </p>
                @if (isset($items) && !$items->isEmpty())
                    <p class="uppercase font-semibold text-2xl sm:text-3xl md:text-4xl">
                        {{ $items->count() }} {{ __('messages.items') }}
                    </p>
                @endif
            </div>
        </x-container>
    </section>

    <!-- Divider with scroll indicator -->
    <div class="w-full h-[1px] bg-brand-primary relative">
        <div class="scroll-indicator aspect-square h-10 bg-brand-primary absolute -top-5 left-1/2 -translate-x-1/2 z-20 rotate-45 center">
            <svg class="text-white text-xl w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    <!-- Main Cart Content -->
    <main class="bg-sage text-gray-900 py-16">
        <x-container class="px-4">
            @if (!isset($items) || $items->isEmpty())
                <!-- Empty Cart State -->
                <div class="empty-cart-state text-center py-20">
                    <div class="w-24 h-24 bg-brand-primary rounded-2xl mx-auto mb-6 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-semibold text-dark mb-4">{{ __('messages.your_cart_is_empty') }}</h2>
                    <p class="text-gray-600 mb-8">{{ __('messages.start_adding_items_to_your_cart_to_see_them_here') }}</p>
                    <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" 
                       class="btn-hero-primary inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        {{ __('messages.continue_shopping') }}
                    </a>
                </div>
            @else
                <!-- Cart Items and Summary -->
                <div class="max-w-[1320px] mx-auto grid gap-10 lg:grid-cols-12">
                    <!-- Cart Items -->
                    <div class="lg:col-span-9 space-y-6">
                        <div class="bg-dark rounded-2xl shadow-soft border border-sage/30 overflow-hidden">
                            <div class="p-6 border-b border-sage/30">
                                <h3 class="text-lg font-semibold text-white">{{ __('messages.cart_items') }}</h3>
                                <p class="text-sm text-sage/80">{{ $items->count() }} {{ __('messages.items_in_your_cart') }}</p>
                            </div>
                            
                            <div class="divide-y divide-sage/30 text-sage">
                            @foreach ($items as $item)
                                    <div class="cart-item p-6 hover:bg-dark/70 transition-colors">
                                        <div class="flex items-center gap-4">
                                            <!-- Product Image -->
                                            <div class="flex-shrink-0">
                                            @if ($thumb = $this->getItemThumbnail($item))
                                                    <img src="{{ $thumb }}" alt="{{ $item->name }}" class="h-20 w-20 object-cover rounded-xl" />
                                                @else
                                                    <div class="h-20 w-20 bg-gray-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                            @endif
                                            </div>

                                            <!-- Product Details -->
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-lg font-semibold mb-2">
                                                    <span class="text-white">{{ $item->name }}</span>
                                                </h4>
                                                <p class="text-sm text-sage/80 mb-3">{{ __('messages.unit_price') }}: {{ \Illuminate\Support\Number::currency((float) $item->price, current_currency(), app()->getLocale()) }}</p>
                                                
                                                <!-- Quantity Controls -->
                                                <div class="flex items-center gap-3">
                                                    <span class="text-sm font-medium text-sage">{{ __('messages.quantity') }}:</span>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" 
                                                                wire:click="decrementItem({{ (int) $item->id }})" 
                                                                wire:loading.attr="disabled"
                                                                class="w-8 h-8 bg-dark/30 hover:bg-dark/40 border border-sage/30 text-sage rounded-lg flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                                title="{{ __('Decrease quantity') }}">
                                                            <svg class="w-4 h-4 text-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                            </svg>
                                                        </button>
                                                        <input type="number" 
                                                               min="0" 
                                                               step="1" 
                                                               value="{{ (int) $item->quantity }}"
                                                               class="w-16 text-center border border-sage/30 bg-dark/30 text-white rounded-lg py-1 focus:outline-none focus:ring-2 focus:ring-sage/30 focus:border-sage/30"
                                                       wire:change="updateItemQuantity({{ (int) $item->id }}, $event.target.value)"
                                                               wire:loading.attr="disabled"
                                                       inputmode="numeric" />
                                                        <button type="button" 
                                                                wire:click="incrementItem({{ (int) $item->id }})" 
                                                                wire:loading.attr="disabled"
                                                                class="w-8 h-8 bg-dark/30 hover:bg-dark/40 border border-sage/30 text-sage rounded-lg flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                                title="{{ __('Increase quantity') }}">
                                                            <svg class="w-4 h-4 text-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                            </svg>
                                                        </button>
                                            </div>
                                        </div>
                                    </div>
                                            
                                            <!-- Price and Actions -->
                                    <div class="text-right">
                                                <p class="text-xl font-semibold text-white mb-2">
                                            {{ \Illuminate\Support\Number::currency((float) $item->price * (int) $item->quantity, current_currency(), app()->getLocale()) }}
                                        </p>
                                        <button wire:click="removeItem({{ (int) $item->id }})" 
                                                wire:confirm="{{ __('translations.confirm_remove_cart_item') }}"
                                                        wire:loading.attr="disabled"
                                                        class="inline-flex items-center gap-2 text-sm text-red-400 hover:text-red-300 hover:underline transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                        title="{{ __('Remove item from cart') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    {{ __('messages.remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                        </div>
                </div>

                    <!-- Cart Summary -->
                    <div class="lg:col-span-3 space-y-6">
                        <!-- Coupon Form -->
                        @if ((bool) (config('app-features.features.discount') ?? true))
                            <div class="bg-dark rounded-2xl shadow-soft border border-sage/30 p-6">
                                <h3 class="text-lg font-semibold text-white mb-4">{{ __('Coupon Code') }}</h3>
                                <div class="coupon-form">
                            <livewire:components.coupon-form />
                                </div>
                            </div>
                        @endif

                        <!-- Cart Total -->
                        <div class="cart-summary bg-dark rounded-2xl shadow-soft border border-sage/30 p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">{{ __('Order Summary') }}</h3>
                            <div class="cart-total">
                            <livewire:components.cart-total />
                        </div>
                    </div>

                        <!-- Checkout Button -->
                        <div class="cart-action-buttons space-y-4">
                        <a wire:navigate
                               href="{{ route('frontend.checkout.index') }}"
                               class="w-full inline-flex items-center justify-center gap-2 rounded-full bg-sage px-6 py-4 text-base font-semibold text-dark hover:bg-sage/90 transition {{ (!isset($items) || $items->isEmpty()) ? 'pointer-events-none opacity-50' : '' }}"
                               @if(!isset($items) || $items->isEmpty()) aria-disabled="true" @endif>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0L17 13m-10 0h10" />
                                </svg>
                                {{ __('Proceed to Checkout') }}
                            </a>
                            
                            <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" 
                               class="w-full inline-flex items-center justify-center gap-2 rounded-full border border-sage/30 px-6 py-4 text-base font-semibold text-sage hover:bg-sage/10 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                {{ __('messages.continue_shopping') }}
                            </a>
                            <p class="text-center text-xs text-gray-600">{{ __('Secure checkout • Encrypted payments') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </x-container>
    </main>
</div>
