<div class="cart-page">
    <!-- Hero Section - Vue-inspired -->
    <section class="relative bg-sage z-10 overflow-hidden">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16 relative">
            <div class="w-full h-max space-y-5 text-dark">
                <div class="flex flex-col md:flex-row justify-between items-center gap-10">
                    <p class="uppercase text-3xl md:text-4xl font-medium text-center sm:text-left">
                        {{ __('Your Cart') }}
                        <span class="block lg:hidden">{{ __('Review your selected items') }}</span>
                    </p>

                    @if (!$items->isEmpty())
                        <button class="btn-hero-secondary w-full md:w-max lg:whitespace-nowrap h-max text-dark border-dark">
                            {{ __('Continue Shopping') }}
                        </button>
                    @endif
                </div>
                <div>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 md:gap-20">
                        <p class="text-sm">
                            {{ __('Review your selected items and proceed to checkout') }}
                        </p>
                        @if (!$items->isEmpty())
                            <p class="hidden lg:block uppercase font-semibold w-full text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl whitespace-normal md:whitespace-nowrap text-center md:text-left">
                                {{ $items->count() }} {{ __('Items') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
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
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            @if ($items->isEmpty())
                <!-- Empty Cart State -->
                <div class="empty-cart-state text-center py-20">
                    <div class="w-24 h-24 bg-brand-primary rounded-2xl mx-auto mb-6 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-semibold text-dark mb-4">{{ __('Your cart is empty') }}</h2>
                    <p class="text-gray-600 mb-8">{{ __('Start adding items to your cart to see them here') }}</p>
                    <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" 
                       class="btn-hero-primary inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        {{ __('Continue Shopping') }}
                    </a>
                </div>
            @else
                <!-- Cart Items and Summary -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-2xl shadow-soft border border-ash/20 overflow-hidden">
                            <div class="p-6 border-b border-ash/20">
                                <h3 class="text-lg font-semibold text-dark">{{ __('Cart Items') }}</h3>
                                <p class="text-sm text-gray-600">{{ $items->count() }} {{ __('items in your cart') }}</p>
                            </div>
                            
                            <div class="divide-y divide-ash/20">
                                @foreach ($items as $item)
                                    <div class="cart-item p-6 hover:bg-gray-50/50 transition-colors">
                                        <div class="flex items-center gap-4">
                                            <!-- Product Image -->
                                            @php($model = $item->associatedModel)
                                            <div class="flex-shrink-0">
                                                @if (method_exists($model, 'getFirstMediaUrl'))
                                                    @php($thumb = $model->getFirstMediaUrl(config('media.storage.thumbnail_collection')) ?: ($model->getFirstMediaUrl(config('media.storage.collection_name'), 'small') ?: ($model->getFirstMediaUrl(config('media.storage.collection_name'), 'medium') ?: $model->getFirstMediaUrl(config('media.storage.collection_name')))))
                                                    @if ($thumb)
                                                        <img src="{{ $thumb }}" alt="{{ $item->name }}" class="h-20 w-20 object-cover rounded-xl" />
                                                    @else
                                                        <div class="h-20 w-20 bg-gray-100 rounded-xl flex items-center justify-center">
                                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                        </div>
                                                    @endif
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
                                                <h4 class="text-lg font-semibold text-dark mb-2">{{ $item->name }}</h4>
                                                <p class="text-sm text-gray-600 mb-3">{{ __('Unit Price') }}: {{ \Illuminate\Support\Number::currency((float) $item->price, current_currency(), app()->getLocale()) }}</p>
                                                
                                                <!-- Quantity Controls -->
                                                <div class="flex items-center gap-3">
                                                    <span class="text-sm font-medium text-gray-700">{{ __('Quantity') }}:</span>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" 
                                                                wire:click="decrementItem({{ (int) $item->id }})" 
                                                                class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
                                                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                            </svg>
                                                        </button>
                                                        <input type="number" 
                                                               min="0" 
                                                               step="1" 
                                                               value="{{ (int) $item->quantity }}"
                                                               class="w-16 text-center border border-gray-200 rounded-lg py-1 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
                                                               wire:change="updateItemQuantity({{ (int) $item->id }}, $event.target.value)"
                                                               inputmode="numeric" />
                                                        <button type="button" 
                                                                wire:click="incrementItem({{ (int) $item->id }})" 
                                                                class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-lg flex items-center justify-center transition-colors">
                                                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Price and Actions -->
                                            <div class="text-right">
                                                <p class="text-xl font-semibold text-dark mb-2">
                                                    {{ \Illuminate\Support\Number::currency((float) $item->price * (int) $item->quantity, current_currency(), app()->getLocale()) }}
                                                </p>
                                                <button wire:click="removeItem({{ (int) $item->id }})" 
                                                        wire:confirm="{{ __('translations.confirm_remove_cart_item') }}"
                                                        class="text-sm text-red-600 hover:text-red-700 hover:underline transition-colors">
                                                    {{ __('Remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Coupon Form -->
                        @if ((bool) (config('app-features.features.discount') ?? true))
                            <div class="bg-white rounded-2xl shadow-soft border border-ash/20 p-6">
                                <h3 class="text-lg font-semibold text-dark mb-4">{{ __('Coupon Code') }}</h3>
                                <div class="coupon-form">
                                    <livewire:components.coupon-form />
                                </div>
                            </div>
                        @endif

                        <!-- Cart Total -->
                        <div class="cart-summary bg-white rounded-2xl shadow-soft border border-ash/20 p-6">
                            <h3 class="text-lg font-semibold text-dark mb-4">{{ __('Order Summary') }}</h3>
                            <div class="cart-total">
                                <livewire:components.cart-total />
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <div class="cart-action-buttons space-y-4">
                            <a href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}"
                               class="btn-hero-primary w-full inline-flex items-center justify-center gap-2 {{ $items->isEmpty() ? 'pointer-events-none opacity-50' : '' }}"
                               @if($items->isEmpty()) aria-disabled="true" @endif>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0L17 13m-10 0h10" />
                                </svg>
                                {{ __('Proceed to Checkout') }}
                            </a>
                            
                            <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" 
                               class="btn-hero-secondary w-full inline-flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                {{ __('Continue Shopping') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>