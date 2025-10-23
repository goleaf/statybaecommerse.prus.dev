<x-layouts.base title="{{ __('Checkout') }}">
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-8">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Checkout') }}</h1>

        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm p-6">
            <h2 class="text-2xl font-semibold mb-4">{{ __('Order summary') }}</h2>
            <dl class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                <div class="flex justify-between">
                    <dt>{{ __('Subtotal') }}</dt>
                    <dd>{{ app_money_format($cart['subtotal']) }}</dd>
                </div>
            @else
                <div class="grid gap-8 lg:grid-cols-5">
                    <div class="lg:col-span-3 space-y-4">
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Order summary') }}</h2>
                            </div>
                            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach($cartItems as $item)
                                    <div class="flex items-start justify-between gap-4 px-6 py-4">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $item->product_snapshot['name'] ?? $item->product?->name }}</p>
                                            @if($sku = $item->product_snapshot['sku'] ?? $item->product?->sku)
                                                <p class="text-sm text-gray-500">SKU: {{ $sku }}</p>
                                            @endif
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ __('Quantity') }}: {{ $item->quantity }}</p>
                                        </div>
                                        <div class="text-right text-sm font-semibold text-gray-900 dark:text-white">{{ app_money_format($item->calculateSubtotal()) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-4">
                        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Payment') }}</h2>
                            <form method="POST" action="{{ route('frontend.checkout.process') }}" class="mt-4 space-y-4">
                                @csrf
                                <div>
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Payment method') }}</label>
                                    <select id="payment_method" name="payment_method" required class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="card">{{ __('Credit or debit card') }}</option>
                                        <option value="bank_transfer">{{ __('Bank transfer') }}</option>
                                        <option value="cod">{{ __('Cash on delivery') }}</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input id="confirm" name="confirm" type="checkbox" value="1" required class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <label for="confirm" class="text-sm text-gray-600 dark:text-gray-300">{{ __('I confirm that my order details are correct.') }}</label>
                                </div>
                                <div class="space-y-2 border-t border-gray-200 pt-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                    <div class="flex justify-between">
                                        <span>{{ __('Subtotal') }}</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $summary['formatted_subtotal'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>{{ __('Estimated tax') }}</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $summary['formatted_tax_amount'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>{{ __('Shipping') }}</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $summary['formatted_shipping_amount'] }}</span>
                                    </div>
                                    @if(($summary['discount_amount'] ?? 0) > 0)
                                        <div class="flex justify-between text-green-600 dark:text-green-400">
                                            <span>{{ __('Discount') }}</span>
                                            <span class="font-semibold">-{{ $summary['formatted_discount_amount'] }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-semibold text-gray-900 dark:border-gray-700 dark:text-white">
                                        <span>{{ __('Total') }}</span>
                                        <span>{{ $summary['formatted_total'] }}</span>
                                    </div>
                                </div>
                                <button type="submit" class="w-full rounded-lg bg-green-600 px-5 py-3 text-white shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    {{ __('Place order') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between">
                    <dt>{{ __('Shipping') }}</dt>
                    <dd>{{ app_money_format($cart['shipping']) }}</dd>
                </div>
                <div class="flex justify-between text-primary-700">
                    <dt>{{ __('Discount') }}</dt>
                    <dd>-{{ app_money_format($cart['discount']) }}</dd>
                </div>
                <div class="flex justify-between text-lg font-semibold text-primary-700">
                    <dt>{{ __('Total') }}</dt>
                    <dd>{{ app_money_format($cart['total']) }}</dd>
                </div>
            </dl>
        </section>

        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm p-6">
            <h2 class="text-2xl font-semibold mb-4">{{ __('Billing & shipping information') }}</h2>
            <form method="POST" action="{{ route('frontend.checkout.process') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Full name') }}</label>
                    <input id="full_name" name="full_name" value="{{ old('full_name', $user->name) }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('full_name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('phone')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="address_line_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Address line 1') }}</label>
                    <input id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('address_line_1')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="address_line_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Address line 2') }}</label>
                    <input id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('address_line_2')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('City') }}</label>
                    <input id="city" name="city" value="{{ old('city') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('city')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Postal code') }}</label>
                    <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('postal_code')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Country') }}</label>
                    <input id="country" name="country" value="{{ old('country', 'Lithuania') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                    @error('country')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Payment method') }}</label>
                    <select id="payment_method" name="payment_method" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                        <option value="card" @selected(old('payment_method') === 'card')>{{ __('Card payment') }}</option>
                        <option value="bank" @selected(old('payment_method') === 'bank')>{{ __('Bank transfer') }}</option>
                        <option value="cod" @selected(old('payment_method') === 'cod')>{{ __('Cash on delivery') }}</option>
                    </select>
                    @error('payment_method')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Order notes') }}</label>
                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('Place order') }}</button>
                </div>
            </form>
        </section>
    </div>
</x-layouts.base>
