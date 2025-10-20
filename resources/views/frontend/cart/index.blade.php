@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-8">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Your cart') }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ __('Review your selected items before proceeding to checkout.') }}</p>
        </header>

        @if (session('status'))
            <div class="rounded-lg bg-green-100 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif

        @if ($items->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-12 text-center text-slate-600 dark:text-slate-300">
                {{ __('Your cart is empty.') }}
            </div>
        @else
            <div class="space-y-6">
                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Cart items') }}</h2>
                    <form method="post" action="{{ route('frontend.cart.update') }}" class="space-y-4">
                        @csrf
                        <div class="space-y-4">
                            @foreach ($items as $item)
                                <div class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3">
                                    <div>
                                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">{{ $item['name'] }}</h3>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('SKU') }}: {{ $item['sku'] }}</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ number_format((float) $item['price'], 2) }} {{ config('app.currency', 'EUR') }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="sr-only" for="quantity-{{ $item['id'] }}">{{ __('Quantity') }}</label>
                                        <input id="quantity-{{ $item['id'] }}" name="items[{{ $loop->index }}][quantity]" type="number" min="1" value="{{ $item['quantity'] }}" class="w-20 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                                        <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item['id'] }}">
                                        <button type="submit"
                                                name="id"
                                                value="{{ $item['id'] }}"
                                                formaction="{{ route('frontend.cart.remove') }}"
                                                formmethod="post"
                                                class="text-sm text-red-600 hover:text-red-700">
                                            {{ __('Remove') }}
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Update cart') }}</button>
                    </form>
                </section>

                <section class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 space-y-4">
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Order summary') }}</h2>
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
                    <div class="flex items-center gap-3">
                        <a href="{{ route('frontend.checkout.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Proceed to checkout') }}</a>
                        <form method="post" action="{{ route('frontend.cart.clear') }}">
                            @csrf
                            <button type="submit" class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-100">{{ __('Clear cart') }}</button>
                        </form>
                    </div>
                </section>
            </div>
        @endif
    </div>
@endsection
