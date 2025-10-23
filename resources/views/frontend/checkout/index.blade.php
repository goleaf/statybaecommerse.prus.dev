@extends('components.layouts.base')

@section('title', __('Checkout'))

@section('content')
    <x-container class="py-8 space-y-8">
        <h1 class="text-3xl font-semibold text-gray-900">{{ __('Checkout') }}</h1>

        <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <form method="post" action="{{ route('frontend.checkout.process') }}" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="{{ __('Name') }}" name="name" value="{{ old('name', auth()->user()?->name) }}" required />
                    <x-input label="{{ __('Email') }}" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" required />
                </div>
                <x-input label="{{ __('Address line 1') }}" name="address" value="{{ old('address') }}" required />
                <x-input label="{{ __('City') }}" name="city" value="{{ old('city') }}" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="{{ __('Postal code') }}" name="postal_code" value="{{ old('postal_code') }}" required />
                    <x-input label="{{ __('Payment method') }}" name="payment_method" value="{{ old('payment_method', 'card') }}" required />
                </div>
                <x-button type="submit">{{ __('Place order') }}</x-button>
            </form>

            <aside class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('Order summary') }}</h2>
                <ul class="space-y-2 text-sm text-gray-600">
                    @foreach ($cart['items'] as $item)
                        <li class="flex justify-between">
                            <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                            <span>{{ \Illuminate\Support\Number::currency($item['total'], current_currency(), app()->getLocale()) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="space-y-1 border-t border-gray-200 pt-4 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>{{ __('Subtotal') }}</span>
                        <span>{{ \Illuminate\Support\Number::currency($cart['subtotal'], current_currency(), app()->getLocale()) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>{{ __('Tax') }}</span>
                        <span>{{ \Illuminate\Support\Number::currency($cart['tax'], current_currency(), app()->getLocale()) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>{{ __('Shipping') }}</span>
                        <span>{{ \Illuminate\Support\Number::currency($cart['shipping'], current_currency(), app()->getLocale()) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>{{ __('Discount') }}</span>
                        <span>-{{ \Illuminate\Support\Number::currency($cart['discount'], current_currency(), app()->getLocale()) }}</span>
                    </div>
                </div>
                <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                    <span class="text-lg font-semibold text-gray-900">{{ __('Total') }}</span>
                    <span class="text-lg font-semibold text-primary-600">
                        {{ \Illuminate\Support\Number::currency($cart['total'], current_currency(), app()->getLocale()) }}
                    </span>
                </div>
            </aside>
        </div>
    </x-container>
@endsection
