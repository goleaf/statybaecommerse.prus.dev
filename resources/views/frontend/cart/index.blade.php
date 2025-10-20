@extends('components.layouts.base')

@section('title', __('Your cart'))

@section('content')
    <x-container class="py-8 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-gray-900">{{ __('Your cart') }}</h1>
            <form method="post" action="{{ route('frontend.cart.clear') }}">
                @csrf
                <x-button type="submit" color="secondary">{{ __('Clear cart') }}</x-button>
            </form>
        </div>

        <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <section class="space-y-4">
                @forelse ($cart['items'] as $item)
                    <article class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-4">
                            @if (!empty($item['slug']))
                                <a href="{{ route('frontend.products.show', ['product' => $item['slug']]) }}" class="font-medium text-gray-900">{{ $item['name'] }}</a>
                            @else
                                <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                            @endif
                            <span class="text-sm text-gray-500">× {{ $item['quantity'] }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ \Illuminate\Support\Number::currency($item['total'], current_currency(), app()->getLocale()) }}
                            </p>
                            <form method="post" action="{{ route('frontend.cart.remove') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Remove') }}</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-12 text-center text-gray-500">
                        {{ __('Your cart is empty.') }}
                    </div>
                @endforelse
            </section>

            <aside class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('Summary') }}</h2>
                <dl class="space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <dt>{{ __('Subtotal') }}</dt>
                        <dd>{{ \Illuminate\Support\Number::currency($cart['subtotal'], current_currency(), app()->getLocale()) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>{{ __('Tax') }}</dt>
                        <dd>{{ \Illuminate\Support\Number::currency($cart['tax'], current_currency(), app()->getLocale()) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>{{ __('Shipping') }}</dt>
                        <dd>{{ \Illuminate\Support\Number::currency($cart['shipping'], current_currency(), app()->getLocale()) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>{{ __('Discount') }}</dt>
                        <dd>-{{ \Illuminate\Support\Number::currency($cart['discount'], current_currency(), app()->getLocale()) }}</dd>
                    </div>
                </dl>
                <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                    <span class="text-lg font-semibold text-gray-900">{{ __('Total') }}</span>
                    <span class="text-lg font-semibold text-primary-600">
                        {{ \Illuminate\Support\Number::currency($cart['total'], current_currency(), app()->getLocale()) }}
                    </span>
                </div>
                <a href="{{ route('frontend.checkout.index') }}" class="inline-flex w-full items-center justify-center rounded-md bg-primary-600 px-4 py-3 text-white hover:bg-primary-700">
                    {{ __('Proceed to checkout') }}
                </a>
            </aside>
        </div>
    </x-container>
@endsection
