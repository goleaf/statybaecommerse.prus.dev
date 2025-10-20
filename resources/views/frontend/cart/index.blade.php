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
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('frontend.cart.update') }}" class="flex items-center gap-2">
                                    @csrf
                                    <label class="text-sm text-gray-500" for="quantity-{{ $loop->index }}">{{ __('Quantity') }}</label>
                                    <input type="number" min="1" name="quantity" id="quantity-{{ $loop->index }}" value="{{ $item['quantity'] }}"
                                           class="w-20 rounded-lg border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-gray-800">
                                    <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">{{ __('Update') }}</button>
                                </form>
                                <form method="POST" action="{{ route('frontend.cart.remove') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-700">{{ __('Remove') }}</button>
                                </form>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-semibold text-primary-600">{{ app_money_format($item['total']) }}</p>
                            </div>
                        </div>
                    @endforeach
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
