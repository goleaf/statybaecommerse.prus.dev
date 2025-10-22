@extends('frontend.layouts.app')

@section('title', __('Available Coupons'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto space-y-8">
            <div class="flex flex-col gap-2 text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ __('Save more with our coupons') }}
                </h1>
                <p class="text-gray-600 dark:text-gray-300">
                    {{ __('Browse the active coupons available to you and apply them directly during checkout.') }}
                </p>
                @if ($hasAppliedCoupon)
                    <span class="inline-flex items-center justify-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                        {{ __('A coupon is currently applied to your cart.') }}
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                @forelse ($coupons as $coupon)
                    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="absolute right-0 top-0 -translate-y-6 translate-x-6 rotate-45 bg-blue-600 px-8 py-1 text-xs font-semibold uppercase tracking-wider text-white">
                            {{ __('Coupon') }}
                        </div>
                        <div class="space-y-4 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $coupon['name'] ?? $coupon['code'] }}</h2>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $coupon['description'] }}</p>
                                </div>
                                <span class="rounded-md bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                                    {{ $coupon['code'] }}
                                </span>
                            </div>

                            <dl class="grid grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-300">
                                <div>
                                    <dt class="font-medium text-gray-900 dark:text-white">{{ __('Discount type') }}</dt>
                                    <dd class="mt-1 capitalize">{{ $coupon['type'] }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-900 dark:text-white">{{ __('Value') }}</dt>
                                    <dd class="mt-1">
                                        @if ($coupon['type'] === 'percentage')
                                            {{ $coupon['value'] }}%
                                        @else
                                            {{ \Illuminate\Support\Number::currency($coupon['value'] ?? 0, currency: current_currency()) }}
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-900 dark:text-white">{{ __('Minimum order amount') }}</dt>
                                    <dd class="mt-1">
                                        @if ($coupon['minimum_amount'])
                                            {{ \Illuminate\Support\Number::currency($coupon['minimum_amount'], currency: current_currency()) }}
                                        @else
                                            {{ __('Not required') }}
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-900 dark:text-white">{{ __('Expires on') }}</dt>
                                    <dd class="mt-1">
                                        @if ($coupon['expires_at'])
                                            {{ \Carbon\Carbon::parse($coupon['expires_at'])->translatedFormat('Y-m-d') }}
                                        @else
                                            {{ __('No expiration') }}
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Apply this code during checkout to activate the discount.') }}
                                </div>
                                <button type="button"
                                        data-code="{{ $coupon['code'] }}"
                                        class="copy-coupon inline-flex items-center rounded-md border border-blue-600 px-3 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 7.5V6a3.75 3.75 0 013.75-3.75h5A3.75 3.75 0 0120.75 6v5a3.75 3.75 0 01-3.75 3.75h-1.5" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 16.5V18a3.75 3.75 0 01-3.75 3.75h-5A3.75 3.75 0 013.25 18v-5A3.75 3.75 0 017 9.25h1.5" />
                                    </svg>
                                    {{ __('Copy code') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-10 text-center dark:border-gray-700 dark:bg-gray-900/40">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ __('No coupons available right now') }}</h2>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Check back soon or subscribe to our newsletter to receive exclusive discount codes.') }}
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('How to redeem a coupon') }}</h2>
                <ol class="mt-4 list-decimal space-y-2 pl-6 text-sm text-gray-600 dark:text-gray-300">
                    <li>{{ __('Add eligible items to your shopping cart.') }}</li>
                    <li>{{ __('Proceed to checkout and locate the coupon form.') }}</li>
                    <li>{{ __('Enter the coupon code exactly as shown and apply it.') }}</li>
                    <li>{{ __('Review the updated totals before completing your order.') }}</li>
                </ol>
            </div>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.copy-coupon').forEach((button) => {
                button.addEventListener('click', () => {
                    navigator.clipboard.writeText(button.dataset.code || '');
                    button.classList.add('bg-blue-600', 'text-white');
                    window.setTimeout(() => {
                        button.classList.remove('bg-blue-600', 'text-white');
                    }, 1200);
                });
            });
        });
    </script>
@endsection
