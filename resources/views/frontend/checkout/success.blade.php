@extends('components.layouts.base')

@section('title', __('Order success'))

@section('content')
    <x-container class="py-12 space-y-6 text-center">
        <h1 class="text-3xl font-semibold text-gray-900">{{ __('Thank you for your order!') }}</h1>
        <p class="text-gray-600">{{ __('Your order reference is :ref.', ['ref' => $receipt['order_reference']]) }}</p>
        <p class="text-gray-600">{{ __('A confirmation email has been sent to :email.', ['email' => $receipt['customer']['email']]) }}</p>
        <a href="{{ route('frontend.products.index') }}" class="inline-flex items-center justify-center rounded-md bg-primary-600 px-4 py-3 text-white hover:bg-primary-700">
            {{ __('Continue shopping') }}
        </a>
    </x-container>
@endsection
