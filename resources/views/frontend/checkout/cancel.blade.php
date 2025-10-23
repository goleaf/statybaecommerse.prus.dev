@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-12 space-y-6 text-center">
        <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Checkout cancelled') }}</h1>
        <p class="text-slate-600 dark:text-slate-300">{{ __('Your checkout session was cancelled. You can return to the cart to make changes or try again.') }}</p>
        <div class="space-x-3">
            <a href="{{ route('frontend.cart.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Return to cart') }}</a>
            <a href="{{ route('frontend.products.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('Continue shopping') }}</a>
        </div>
    </div>
@endsection
