@extends('components.layouts.base')

@section('title', __('Order confirmation'))

@section('content')
    <div class="container mx-auto px-4 py-10">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="rounded-xl border border-green-200 bg-green-50 p-6 text-green-900 dark:border-green-700 dark:bg-green-900/40 dark:text-green-100">
                <h1 class="text-2xl font-bold">{{ __('Order confirmed') }}</h1>
                <p class="mt-2">{{ __('Thank you for your purchase! A confirmation email has been sent with the details of your order.') }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Order details') }}</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Order number') }}: <span class="font-semibold text-gray-900 dark:text-white">{{ $order->number }}</span></p>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($order->items as $item)
                        <div class="flex items-start justify-between gap-4 px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</p>
                                <p class="text-sm text-gray-500">SKU: {{ $item->sku }}</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ __('Quantity') }}: {{ $item->quantity }}</p>
                            </div>
                            <div class="text-right text-sm font-semibold text-gray-900 dark:text-white">{{ app_money_format($item->total) }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between px-6 py-4 text-lg font-semibold text-gray-900 dark:text-white">
                    <span>{{ __('Order total') }}</span>
                    <span>{{ app_money_format($order->total) }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('frontend.orders.index') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{ __('View my orders') }}
                </a>
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:border-blue-500 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{ __('Continue shopping') }}
                </a>
            </div>
        </div>
    </div>
@endsection
