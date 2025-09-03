<div class="flex flex-col h-full divide-y divide-gray-200">
    <div class="flex-1 h-0 py-6 overflow-y-auto">
        {{-- Enhanced Header --}}
        <header class="px-4 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7M7 18h10" />
                    </svg>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('My cart') }}</h2>
                </div>
                <button 
                    wire:click="$dispatch('closePanel')"
                    class="rounded-md p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    title="{{ __('Close panel') }}"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </header>

        <div class="flex-1 px-4 mt-8 sm:px-6">
            @if ($items->isNotempty())
                <div class="flow-root">
                    <ul role="list" class="-my-6 divide-y divide-gray-200">
                        @foreach ($items as $item)
                            <x-cart.item wire:key="{{ $item->id }}" :item="$item" />
                        @endforeach
                    </ul>
                </div>
            @else
                {{-- Enhanced Empty Cart State --}}
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7M7 18h10" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Your cart is empty') }}</h3>
                    <p class="text-gray-500 mb-6">{{ __('Browse our product catalog to find your perfect match.') }}</p>
                    
                    <a 
                        href="{{ route('products.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        {{ __('Start Shopping') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
    <div class="p-4 space-y-4 sm:p-6">
        <div class="text-sm text-gray-500">
            <div class="flex items-center justify-between pb-1 mb-3 border-b border-gray-200">
                <p>{{ __('Tax') }}</p>
                <p class="text-base text-right text-gray-950">
                    {{ shopper_money_format(0, currency: current_currency()) }}
                </p>
            </div>
            <div class="flex items-center justify-between pt-1 pb-1 mb-3 border-b border-gray-200">
                <p>{{ __('Delivery') }}</p>
                <p class="text-right">{{ __('Calculated at the time of payment') }}</p>
            </div>
            <div class="flex items-center justify-between pt-1 pb-1 mb-3 border-b border-gray-200">
                <p>{{ __('Subtotal') }}</p>
                <p class="text-base text-right text-gray-950">
                    {{ shopper_money_format($subtotal, currency: current_currency()) }}
                </p>
            </div>
        </div>
        <a 
            href="{{ route('checkout.index', ['locale' => app()->getLocale()]) }}"
            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-4 text-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            wire:loading.attr="disabled"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            {{ __('Proceed to checkout') }}
        </a>
    </div>
</div>
