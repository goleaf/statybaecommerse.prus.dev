<x-layouts.base title="{{ __('Checkout') }}">
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-8">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Checkout') }}</h1>

        @php
            $isCartEmpty = $cartItems->isEmpty();
        @endphp

        @if($isCartEmpty)
            <section class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Your cart is empty') }}</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Add some products to your cart before proceeding to checkout.') }}
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('frontend.cart.index') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                        {{ __('View cart') }}
                    </a>
                    <a href="{{ route('frontend.products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-gray-800">
                        {{ __('Continue shopping') }}
                    </a>
                </div>
            </section>
        @else
            <div class="grid gap-8 lg:grid-cols-5">
                <div class="lg:col-span-3 space-y-4">
                    <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Order summary') }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ trans_choice(':count item|:count items', $summary['item_count'] ?? $cartItems->sum('quantity'), ['count' => $summary['item_count'] ?? $cartItems->sum('quantity')]) }}
                            </p>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($cartItems as $item)
                                @php
                                    $snapshot = (array) $item->product_snapshot;
                                    $product = $item->product;
                                    $name = $snapshot['name'] ?? $product?->name ?? __('Unknown product');
                                    $sku = $snapshot['sku'] ?? $product?->sku;
                                @endphp
                                <div class="flex items-start justify-between gap-4 px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $name }}</p>
                                        @if($sku)
                                            <p class="text-sm text-gray-500">{{ __('SKU') }}: {{ $sku }}</p>
                                        @endif
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ __('Quantity') }}: {{ $item->quantity }}</p>
                                    </div>
                                    <div class="text-right text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ app_money_format($item->calculateSubtotal(), $summary['currency'] ?? config('app.currency', 'EUR')) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <div class="lg:col-span-2 space-y-4">
                    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Totals') }}</h2>
                        <dl class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex justify-between">
                                <dt>{{ __('Subtotal') }}</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">{{ $summary['formatted_subtotal'] ?? app_money_format($summary['subtotal'] ?? 0) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>{{ __('Tax') }}</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">{{ $summary['formatted_tax_amount'] ?? app_money_format($summary['tax_amount'] ?? 0) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>{{ __('Shipping') }}</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">{{ $summary['formatted_shipping_amount'] ?? app_money_format($summary['shipping_amount'] ?? 0) }}</dd>
                            </div>
                            @if(($summary['discount_amount'] ?? 0.0) > 0)
                                <div class="flex justify-between text-red-600 dark:text-red-400">
                                    <dt>{{ __('Discounts') }}</dt>
                                    <dd class="font-semibold">-{{ $summary['formatted_discount_amount'] ?? app_money_format($summary['discount_amount'] ?? 0) }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-semibold text-gray-900 dark:border-gray-700 dark:text-white">
                                <dt>{{ __('Total') }}</dt>
                                <dd>{{ $summary['formatted_total'] ?? app_money_format($summary['total'] ?? 0) }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Billing & shipping details') }}</h2>
                        <form method="POST" action="{{ route('frontend.checkout.process') }}" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            @csrf

                            <div class="md:col-span-2">
                                <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Full name') }}</label>
                                <input id="full_name" name="full_name" value="{{ old('full_name') }}" required class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">
                                @error('full_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email') }}</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Phone') }}</label>
                                <input id="phone" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="address_line_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Address line 1') }}</label>
                                <input id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}" required class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">
                                @error('address_line_1')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="address_line_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Address line 2') }}</label>
                                <input id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">
                                @error('address_line_2')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('City') }}</label>
                                <input id="city" name="city" value="{{ old('city') }}" required class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">
                                @error('city')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Postal code') }}</label>
                                <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">
                                @error('postal_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2 md:col-start-1">
                                <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Country') }}</label>
                                <input id="country" name="country" value="{{ old('country', 'Lithuania') }}" required class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">
                                @error('country')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Payment method') }}</label>
                                <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="card" @selected(old('payment_method') === 'card')>{{ __('Credit or debit card') }}</option>
                                    <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>{{ __('Bank transfer') }}</option>
                                    <option value="cod" @selected(old('payment_method') === 'cod')>{{ __('Cash on delivery') }}</option>
                                </select>
                                @error('payment_method')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Order notes') }}</label>
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2 flex justify-end">
                                <button type="submit" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700">
                                    {{ __('Place order') }}
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        @endif
    </div>
</x-layouts.base>
