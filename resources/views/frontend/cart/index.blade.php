<x-layouts.base title="{{ __('Your cart') }}">
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-8">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Shopping cart') }}</h1>

        @if ($items)
            <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm">
                <div class="p-6 space-y-4 divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($items as $item)
                        <div class="pt-4 first:pt-0 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $item['name'] }}</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Unit price: :price', ['price' => app_money_format($item['price'])]) }}</p>
                            </div>
                            <div class="flex items-center justify-end gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <dt>{{ __('Tax') }}:</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">{{ $summary['formatted_tax_amount'] }}</dd>
                            </div>
                            <div class="flex items-center justify-end gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <dt>{{ __('Shipping') }}:</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">{{ $summary['formatted_shipping_amount'] }}</dd>
                            </div>
                            @if(($summary['discount_amount'] ?? 0) > 0)
                                <div class="flex items-center justify-end gap-3 text-sm text-green-600 dark:text-green-400">
                                    <dt>{{ __('Discount') }}:</dt>
                                    <dd class="font-semibold">-{{ $summary['formatted_discount_amount'] }}</dd>
                                </div>
                            @endif
                            <div class="flex items-center justify-end gap-3 text-base font-semibold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700 pt-2">
                                <dt>{{ __('Total') }}:</dt>
                                <dd>{{ $summary['formatted_total'] }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('frontend.checkout.index') }}"
                           class="inline-flex items-center justify-center rounded-lg bg-green-600 px-5 py-3 text-center text-white shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            {{ __('Proceed to checkout') }}
                        </a>
                        <a href="{{ route('products.index', ['locale' => app()->getLocale()]) ?? '/products' }}"
                           class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-center text-gray-700 hover:border-blue-500 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{ __('Continue shopping') }}
                        </a>
                    </div>
                </div>
            </section>
        @else
            <p class="text-gray-500 dark:text-gray-400">{{ __('Your cart is empty.') }}</p>
        @endif

        <section class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm p-6">
            <h2 class="text-2xl font-semibold mb-4">{{ __('Order summary') }}</h2>
            <dl class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                <div class="flex justify-between">
                    <dt>{{ __('Subtotal') }}</dt>
                    <dd>{{ app_money_format($subtotal) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>{{ __('Tax') }}</dt>
                    <dd>{{ app_money_format($tax) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>{{ __('Shipping') }}</dt>
                    <dd>{{ app_money_format($shipping) }}</dd>
                </div>
                <div class="flex justify-between text-primary-700">
                    <dt>{{ __('Discount') }}</dt>
                    <dd>-{{ app_money_format($discount) }}</dd>
                </div>
                <div class="flex justify-between text-lg font-semibold text-primary-700">
                    <dt>{{ __('Total') }}</dt>
                    <dd>{{ app_money_format($total) }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-4">
                <form method="POST" action="{{ route('frontend.cart.clear') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">{{ __('Clear cart') }}</button>
                </form>

                <form method="POST" action="{{ route('frontend.discounts.apply-coupon') }}" class="flex items-center gap-2">
                    @csrf
                    <label for="code" class="text-sm text-gray-500">{{ __('Coupon code') }}</label>
                    <input id="code" name="code" class="rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800" placeholder="{{ __('Enter code') }}">
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('Apply') }}</button>
                </form>

                <form method="POST" action="{{ route('frontend.discounts.remove-coupon') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">{{ __('Remove coupon') }}</button>
                </form>

                @auth
                    <a href="{{ route('frontend.checkout.index') }}" class="ml-auto inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('Proceed to checkout') }}</a>
                @endauth
            </div>
        </section>
    </div>
</x-layouts.base>
