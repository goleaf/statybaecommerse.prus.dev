@extends('components.layouts.base')

@section('title', __('Coupons'))

@section('content')
    <x-container class="py-8 space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900">{{ __('Coupons') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('Apply a coupon code to receive a discount on your order.') }}</p>
        </div>

        <form method="post" action="{{ route('frontend.discounts.apply-coupon') }}" class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center">
            @csrf
            <x-input label="{{ __('Coupon code') }}" name="code" value="{{ old('code', session('applied_coupon')) }}" required class="sm:flex-1" />
            <x-button type="submit">{{ __('Apply coupon') }}</x-button>
        </form>

        <div class="space-y-4">
            @forelse ($coupons as $coupon)
                <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">{{ $coupon->name ?? $coupon->code }}</h2>
                        <span class="text-sm text-gray-500">{{ strtoupper($coupon->code) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">{{ $coupon->description }}</p>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Value: :value', ['value' => $coupon->value]) }}</p>
                    @if ($coupon->expires_at)
                        <p class="text-sm text-gray-500">{{ __('Expires at :date', ['date' => $coupon->expires_at->format('Y-m-d')]) }}</p>
                    @endif
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 p-12 text-center text-gray-500">
                    {{ __('No public coupons are currently available.') }}
                </div>
            @endforelse
        </div>

        @if (session('applied_coupon'))
            <form method="post" action="{{ route('frontend.discounts.remove-coupon') }}" class="text-center">
                @csrf
                <x-button type="submit" color="secondary">{{ __('Remove applied coupon') }}</x-button>
            </form>
        @endif
    </x-container>
@endsection
