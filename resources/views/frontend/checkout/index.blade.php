@extends('components.layouts.base')

@section('title', __('Checkout'))

@section('content')
    <div class="container mx-auto px-4 py-10">
        <div class="max-w-5xl mx-auto space-y-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Checkout') }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Confirm your details and place your order securely.') }}</p>
            </div>

            @if($cartItems->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-white/60 dark:bg-gray-900/40 p-12 text-center">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Your cart is empty') }}</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Add items to your cart before heading to checkout.') }}</p>
                    <a href="{{ route('frontend.cart.index') }}"
                       class="mt-6 inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ __('Return to cart') }}
                    </a>
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
            @endif
        </div>
    </div>
@endsection
