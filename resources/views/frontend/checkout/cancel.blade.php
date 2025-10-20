@extends('components.layouts.base')

@section('title', __('Order cancelled'))

@section('content')
    <x-container class="py-12 space-y-6 text-center">
        <h1 class="text-3xl font-semibold text-gray-900">{{ __('Checkout cancelled') }}</h1>
        <p class="text-gray-600">{{ __('Your checkout session was cancelled. You can resume shopping below.') }}</p>
        <a href="{{ route('frontend.cart.index') }}" class="inline-flex items-center justify-center rounded-md bg-primary-600 px-4 py-3 text-white hover:bg-primary-700">
            {{ __('Return to cart') }}
        </a>
    </x-container>
@endsection
