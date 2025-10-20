@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-10">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Checkout') }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ __('Provide your billing details to complete the order.') }}</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <form method="post" action="{{ route('frontend.checkout.process') }}" class="space-y-6">
                    @csrf
                    <section class="space-y-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6">
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Customer information') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="name">{{ __('Full name') }}</label>
                                <input id="name" name="name" value="{{ old('name', $user?->name) }}" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="email">{{ __('Email') }}</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="phone">{{ __('Phone number') }}</label>
                                <input id="phone" name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6">
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Billing details') }}</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="billing_address">{{ __('Billing address') }}</label>
                                <textarea id="billing_address" name="billing_address" rows="3" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">{{ old('billing_address', $user?->addresses()->default()->first()?->full_address) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="shipping_address">{{ __('Shipping address (optional)') }}</label>
                                <textarea id="shipping_address" name="shipping_address" rows="3" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">{{ old('shipping_address') }}</textarea>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6">
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Payment') }}</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="payment_method">{{ __('Payment method') }}</label>
                                <select id="payment_method" name="payment_method" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                                    <option value="card">{{ __('Credit or debit card') }}</option>
                                    <option value="bank">{{ __('Bank transfer') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="notes">{{ __('Order notes (optional)') }}</label>
                                <textarea id="notes" name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </section>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Place order') }}</button>
                    </div>
                </form>
            </div>

            <aside class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 space-y-4">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Order summary') }}</h2>
                <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
                    @forelse ($items as $item)
                        <li class="flex items-center justify-between">
                            <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                            <span>{{ number_format((float) $item['total'], 2) }} {{ config('app.currency', 'EUR') }}</span>
                        </li>
                    @empty
                        <li>{{ __('Your cart is empty.') }}</li>
                    @endforelse
                </ul>
                <dl class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <div class="flex justify-between">
                        <dt>{{ __('Subtotal') }}</dt>
                        <dd>{{ number_format($summary['subtotal'], 2) }} {{ config('app.currency', 'EUR') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>{{ __('Tax') }}</dt>
                        <dd>{{ number_format($summary['tax'], 2) }} {{ config('app.currency', 'EUR') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>{{ __('Shipping') }}</dt>
                        <dd>{{ number_format($summary['shipping'], 2) }} {{ config('app.currency', 'EUR') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>{{ __('Discount') }}</dt>
                        <dd>-{{ number_format($summary['discount'], 2) }} {{ config('app.currency', 'EUR') }}</dd>
                    </div>
                    <div class="flex justify-between text-base font-semibold text-slate-900 dark:text-slate-100">
                        <dt>{{ __('Total') }}</dt>
                        <dd>{{ number_format($summary['total'], 2) }} {{ config('app.currency', 'EUR') }}</dd>
                    </div>
                </dl>
            </aside>
        </div>
    </div>
@endsection
