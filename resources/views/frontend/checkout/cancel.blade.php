@extends('components.layouts.base')

@section('title', __('Checkout cancelled'))

@section('content')
    <div class="container mx-auto px-4 py-10">
        <div class="max-w-3xl mx-auto space-y-6 text-center">
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-6 text-yellow-900 dark:border-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-100">
                <h1 class="text-2xl font-bold">{{ __('Checkout cancelled') }}</h1>
                <p class="mt-2">{{ __('Your payment was cancelled. You can resume shopping or return to your cart to try again.') }}</p>
            </div>

            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('frontend.cart.index') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{ __('Return to cart') }}
                </a>
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:border-blue-500 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{ __('Continue shopping') }}
                </a>
            </div>
        </div>
    </div>
@endsection
