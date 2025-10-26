<div class="space-y-10">
    {{-- Checkout heading communicates the active step to the shopper. --}}
    <div class="space-y-1">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('frontend.checkout.title') }}</h1>
        <p class="text-sm text-gray-600">{{ __('frontend.checkout.guest_checkout') }}</p>
    </div>

    <form wire:submit.prevent="placeOrder" class="grid gap-10 lg:grid-cols-3">
        {{-- Left column groups the address and shipping selections. --}}
        <div class="space-y-8 lg:col-span-2">
            <section class="space-y-4">
                {{-- Billing address block keeps guest checkout intuitive. --}}
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('frontend.checkout.billing_address') }}</h2>
                    <p class="text-sm text-gray-600">{{ __('frontend.checkout.fill_billing_address') }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="flex flex-col space-y-1 text-sm text-gray-700">
                        <span>{{ __('frontend.checkout.first_name') }}</span>
                        <input
                            type="text"
                            wire:model.live="billing.first_name"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('billing.first_name')
                        <span class="text-xs text-red-600">{{ __($message) }}</span>
                        @enderror
                    </label>

                    <label class="flex flex-col space-y-1 text-sm text-gray-700">
                        <span>{{ __('frontend.checkout.last_name') }}</span>
                        <input
                            type="text"
                            wire:model.live="billing.last_name"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('billing.last_name')
                        <span class="text-xs text-red-600">{{ __($message) }}</span>
                        @enderror
                    </label>

                    <label class="flex flex-col space-y-1 text-sm text-gray-700">
                        <span>{{ __('frontend.checkout.email') }}</span>
                        <input
                            type="email"
                            wire:model.live="billing.email"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('billing.email')
                        <span class="text-xs text-red-600">{{ __($message) }}</span>
                        @enderror
                    </label>

                    <label class="flex flex-col space-y-1 text-sm text-gray-700">
                        <span>{{ __('frontend.checkout.phone') }}</span>
                        <input
                            type="tel"
                            wire:model.live="billing.phone"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('billing.phone')
                        <span class="text-xs text-red-600">{{ __($message) }}</span>
                        @enderror
                    </label>

                    <label class="flex flex-col space-y-1 text-sm text-gray-700 sm:col-span-2">
                        <span>{{ __('frontend.checkout.company') }}</span>
                        <input
                            type="text"
                            wire:model.live="billing.company"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </label>

                    <label class="flex flex-col space-y-1 text-sm text-gray-700 sm:col-span-2">
                        <span>{{ __('frontend.checkout.address') }}</span>
                        <input
                            type="text"
                            wire:model.live="billing.address"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('billing.address')
                        <span class="text-xs text-red-600">{{ __($message) }}</span>
                        @enderror
                    </label>

                    <label class="flex flex-col space-y-1 text-sm text-gray-700">
                        <span>{{ __('frontend.checkout.city') }}</span>
                        <input
                            type="text"
                            wire:model.live="billing.city"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('billing.city')
                        <span class="text-xs text-red-600">{{ __($message) }}</span>
                        @enderror
                    </label>

                    <label class="flex flex-col space-y-1 text-sm text-gray-700">
                        <span>{{ __('frontend.checkout.state') }}</span>
                        <input
                            type="text"
                            wire:model.live="billing.region"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                    </label>

                    <label class="flex flex-col space-y-1 text-sm text-gray-700">
                        <span>{{ __('frontend.checkout.postal_code') }}</span>
                        <input
                            type="text"
                            wire:model.live="billing.postal_code"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('billing.postal_code')
                        <span class="text-xs text-red-600">{{ __($message) }}</span>
                        @enderror
                    </label>

                    <label class="flex flex-col space-y-1 text-sm text-gray-700">
                        <span>{{ __('frontend.checkout.country') }}</span>
                        <select
                            wire:model.live="billing.country"
                            class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                            @foreach($countries as $country)
                                <option value="{{ data_get($country, 'code') }}">{{ data_get($country, 'name') }}</option>
                            @endforeach
                        </select>
                        @error('billing.country')
                        <span class="text-xs text-red-600">{{ __($message) }}</span>
                        @enderror
                    </label>
                </div>
            </section>

            <section class="space-y-4">
                {{-- Shipping address block mirrors billing but respects the toggle. --}}
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('frontend.checkout.shipping_address') }}</h2>
                        <p class="text-sm text-gray-600">{{ __('frontend.checkout.select_shipping_address') }}</p>
                    </div>
                    <label class="flex items-center space-x-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model.live="sameAsShipping" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                        <span>{{ __('frontend.checkout.same_as_billing') }}</span>
                    </label>
                </div>

                @if(! $sameAsShipping)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex flex-col space-y-1 text-sm text-gray-700">
                            <span>{{ __('frontend.checkout.first_name') }}</span>
                            <input
                                type="text"
                                wire:model.live="shipping.first_name"
                                class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                            @error('shipping.first_name')
                            <span class="text-xs text-red-600">{{ __($message) }}</span>
                            @enderror
                        </label>

                        <label class="flex flex-col space-y-1 text-sm text-gray-700">
                            <span>{{ __('frontend.checkout.last_name') }}</span>
                            <input
                                type="text"
                                wire:model.live="shipping.last_name"
                                class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                            @error('shipping.last_name')
                            <span class="text-xs text-red-600">{{ __($message) }}</span>
                            @enderror
                        </label>

                        <label class="flex flex-col space-y-1 text-sm text-gray-700 sm:col-span-2">
                            <span>{{ __('frontend.checkout.address') }}</span>
                            <input
                                type="text"
                                wire:model.live="shipping.address"
                                class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                            @error('shipping.address')
                            <span class="text-xs text-red-600">{{ __($message) }}</span>
                            @enderror
                        </label>

                        <label class="flex flex-col space-y-1 text-sm text-gray-700">
                            <span>{{ __('frontend.checkout.city') }}</span>
                            <input
                                type="text"
                                wire:model.live="shipping.city"
                                class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                            @error('shipping.city')
                            <span class="text-xs text-red-600">{{ __($message) }}</span>
                            @enderror
                        </label>

                        <label class="flex flex-col space-y-1 text-sm text-gray-700">
                            <span>{{ __('frontend.checkout.state') }}</span>
                            <input
                                type="text"
                                wire:model.live="shipping.region"
                                wire:change="refreshShippingOptions(true)"
                                class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                        </label>

                        <label class="flex flex-col space-y-1 text-sm text-gray-700">
                            <span>{{ __('frontend.checkout.postal_code') }}</span>
                            <input
                                type="text"
                                wire:model.live="shipping.postal_code"
                                wire:change="refreshShippingOptions(true)"
                                class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                            @error('shipping.postal_code')
                            <span class="text-xs text-red-600">{{ __($message) }}</span>
                            @enderror
                        </label>

                        <label class="flex flex-col space-y-1 text-sm text-gray-700">
                            <span>{{ __('frontend.checkout.country') }}</span>
                            <select
                                wire:model.live="shipping.country"
                                wire:change="refreshShippingOptions(true)"
                                class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                                @foreach($countries as $country)
                                    <option value="{{ data_get($country, 'code') }}">{{ data_get($country, 'name') }}</option>
                                @endforeach
                            </select>
                            @error('shipping.country')
                            <span class="text-xs text-red-600">{{ __($message) }}</span>
                            @enderror
                        </label>
                    </div>
                @endif
            </section>

            <section class="space-y-4">
                {{-- Shipping options from the resolver are rendered with labels and ETAs. --}}
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('frontend.checkout.shipping_method') }}</h2>
                    <p class="text-sm text-gray-600">{{ __('frontend.checkout.estimated_delivery') }}</p>
                </div>

                <div wire:loading.delay.shortest wire:target="shipping.country,shipping.region,shipping.postal_code,refreshShippingOptions" class="rounded-md bg-primary-50 px-4 py-2 text-sm text-primary-700">
                    {{ __('cart.loading_rates') }}
                </div>

                @if(! empty($availableShippingOptions))
                    <div class="space-y-3">
                        @foreach($availableShippingOptions as $option)
                            <label class="flex cursor-pointer items-start space-x-3 rounded-md border border-gray-200 px-4 py-3 text-sm hover:border-primary-400">
                                <input
                                    type="radio"
                                    wire:model.live="selectedShippingOption"
                                    value="{{ data_get($option, 'id') }}"
                                    class="mt-1 h-4 w-4 border-gray-300 text-primary-500 focus:ring-primary-500"
                                >
                                <div class="flex flex-col space-y-1">
                                    <span class="font-medium text-gray-900">{{ data_get($option, 'name') }}</span>
                                    <span class="text-gray-600">{{ data_get($option, 'formatted_price') }}</span>
                                    @if(data_get($option, 'estimated_delivery'))
                                        <span class="text-xs text-gray-500">{{ data_get($option, 'estimated_delivery') }}</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="rounded-md bg-red-50 px-4 py-2 text-sm text-red-700">
                        {{ __('frontend.checkout.no_shipping_available') }}
                    </p>
                @endif

                @error('selectedShippingOption')
                <p class="text-sm text-red-600">{{ __($message) }}</p>
                @enderror
            </section>

            <section class="space-y-4">
                {{-- Payment methods follow the resolver order for clarity. --}}
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('frontend.checkout.payment_method') }}</h2>
                    <p class="text-sm text-gray-600">{{ __('frontend.checkout.choose_payment_method') }}</p>
                </div>

                <div class="space-y-3">
                    @foreach($paymentMethods as $value => $label)
                        <label class="flex cursor-pointer items-center space-x-3 rounded-md border border-gray-200 px-4 py-3 text-sm hover:border-primary-400">
                            <input
                                type="radio"
                                wire:model.live="selectedPaymentMethod"
                                value="{{ $value }}"
                                class="h-4 w-4 border-gray-300 text-primary-500 focus:ring-primary-500"
                            >
                            <span class="text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                @error('selectedPaymentMethod')
                <p class="text-sm text-red-600">{{ __($message) }}</p>
                @enderror
            </section>

            <section class="space-y-3">
                {{-- Notes provide additional context for fulfilment teams. --}}
                <label class="flex flex-col space-y-1 text-sm text-gray-700">
                    <span>{{ __('frontend.checkout.order_notes') }}</span>
                    <textarea
                        rows="3"
                        wire:model.live="notes"
                        class="rounded-md border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    ></textarea>
                </label>

                <div class="flex items-center justify-end space-x-4">
                    <button
                        type="button"
                        wire:click="previousStep"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        {{ __('pagination.previous') }}
                    </button>

                    <button
                        type="button"
                        wire:click="nextStep"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                    >
                        {{ __('pagination.next') }}
                    </button>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="refreshShippingOptions,placeOrder"
                        class="rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500 disabled:cursor-not-allowed disabled:bg-primary-300"
                    >
                        {{ __('frontend.checkout.place_order') }}
                    </button>
                </div>
            </section>
        </div>

        {{-- Summary column keeps totals visible on larger screens. --}}
        <aside class="space-y-6 rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('frontend.checkout.order_summary') }}</h2>

            <div class="space-y-2 text-sm text-gray-700">
                <div class="flex items-center justify-between">
                    <span>{{ __('frontend.cart.subtotal') }}</span>
                    <span>{{ data_get($summary, 'formatted_subtotal') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>{{ __('frontend.cart.shipping') }}</span>
                    <span>{{ data_get($summary, 'formatted_shipping_amount') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>{{ __('frontend.cart.tax') }}</span>
                    <span>{{ data_get($summary, 'formatted_tax_amount') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>{{ __('frontend.cart.discount') }}</span>
                    <span>{{ data_get($summary, 'formatted_discount_amount') }}</span>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 pt-4 text-base font-semibold text-gray-900">
                <span>{{ __('frontend.cart.total') }}</span>
                <span>{{ data_get($summary, 'formatted_total') }}</span>
            </div>

            <div class="space-y-3">
                {{-- Cart items list reminds guests what they are purchasing. --}}
                @foreach($cartItems as $item)
                    <div class="flex items-start justify-between text-sm text-gray-700">
                        <div class="flex flex-col">
                            <span class="font-medium">{{ $item->product?->name ?? $item->product_snapshot['name'] ?? __('frontend.cart.product') }}</span>
                            <span class="text-xs text-gray-500">{{ __('frontend.cart.quantity') }}: {{ $item->quantity }}</span>
                        </div>
                        <span>{{ app_money_format((float) $item->total_price, data_get($summary, 'currency')) }}</span>
                    </div>
                @endforeach
            </div>
        </aside>
    </form>
</div>
