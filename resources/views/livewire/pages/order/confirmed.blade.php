@extends('components.layouts.base', ['title' => __('Order Confirmed')])

@section('meta')
    <x-meta robots="noindex" canonical="{{ url()->current() }}" />
@endsection

@section('content')
    <x-container class="py-16">
        <h1 class="text-2xl font-semibold">{{ __('Thank you! Your order is confirmed.') }}</h1>
        <p class="mt-2 text-gray-600">{{ __('Order number:') }} {{ $order->number }}</p>

        <div class="mt-8 space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-gray-500">{{ __('Subtotal') }}</span>
                <span>{{ \Illuminate\Support\Number::currency($order->subtotal_amount, $order->currency_code, app()->getLocale()) }}</span>
            </div>
            @if (($order->discount_total_amount ?? 0) > 0)
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">{{ __('Discounts') }}</span>
                    <span>-{{ \Illuminate\Support\Number::currency($order->discount_total_amount, $order->currency_code, app()->getLocale()) }}</span>
                </div>
                @if (isset($redemptions) && $redemptions->isNotEmpty())
                    <ul class="text-sm text-gray-500">
                        @foreach ($redemptions as $r)
                            <li>{{ strtoupper($r->code) }} — {{ ucfirst($r->type) }}:
                                -{{ \Illuminate\Support\Number::currency($r->amount_saved, $order->currency_code, app()->getLocale()) }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endif
            <div class="flex items-center justify-between">
                <span class="text-gray-500">{{ __('Shipping') }}</span>
                <span>{{ \Illuminate\Support\Number::currency($order->shipping_total_amount, $order->currency_code, app()->getLocale()) }}</span>
            </div>
            <div class="flex items-center justify-between font-semibold border-t border-gray-200 pt-4">
                <span>{{ __('Total') }}</span>
                <span>{{ \Illuminate\Support\Number::currency($order->grand_total_amount, $order->currency_code, app()->getLocale()) }}</span>
            </div>
        </div>
    </x-container>
@endsection

<?php
// Legacy Shopper\Core\Models\Order removed - using App\Models\Order

use function Livewire\Volt\{mount, state, layout};

layout('components.layouts.base');

state(['order' => null]);

mount(function (string $number): void {
    $this->order = Order::with(['items', 'items.product', 'shippingOption', 'shippingAddress', 'paymentMethod'])
        ->where('number', $number)
        ->firstOrFail();
});

?>

<x-container class="py-16 sm:pb-32 sm:pt-24 lg:max-w-4xl">
    <!-- Session Status -->
    <x-alert.success class="mb-4" :status="session('status')" />
    <x-alert.error class="mb-4" :status="session('error')" />

    <div class="max-w-xl">
        <p class="text-2xl font-medium tracking-tight text-gray-900">
            {{ __('Thank you!') }}
        </p>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
            {{ __('Your order has been placed successfully') }}
        </h1>
        <p class="mt-2 text text-gray-500">
            {{ __('The details of your order have been sent to you by email.') }}
        </p>
    </div>

    <div class="mt-10 space-y-10 sm:mt-14 sm:space-y-14">
        <div>
            <h3 class="sr-only">
                {{ __('Order place on') }}
                <time datetime="{{ format_datetime($order->created_at) }}" class="capitalize">
                    {{ format_datetime($order->created_at) }}
                </time>
            </h3>
            <div
                 class="bg-gray-50/50 ring-1 ring-gray-100 px-4 py-6 sm:p-6 md:flex md:items-center md:justify-between md:space-x-6 lg:space-x-8">
                <dl
                    class="flex-auto space-y-4 divide-y divide-gray-200 text-sm text-gray-500 md:grid md:grid-cols-3 md:gap-x-6 md:space-y-0 md:divide-y-0 lg:w-1/2 lg:flex-none lg:gap-x-8">
                    <div class="flex justify-between md:block">
                        <dt class="font-medium text-gray-900">
                            {{ __('Order N°') }}
                        </dt>
                        <dd class="uppercase md:mt-1">{{ $order->number }}</dd>
                    </div>
                    <div class="flex justify-between pt-4 md:block md:pt-0">
                        <dt class="font-medium text-gray-900">
                            {{ __('Place on') }}
                        </dt>
                        <dd class="md:mt-1 capitalize">
                            <time datetime="{{ format_datetime($order->created_at) }}">
                                {{ format_datetime($order->created_at) }}
                            </time>
                        </dd>
                    </div>
                    <div class="flex justify-between pt-4 font-medium text-gray-900 lg:block md:pt-0">
                        <dt class="font-semibold">{{ __('Total') }}</dt>
                        <dd class="md:mt-1">
                            {{ \Illuminate\Support\Number::currency($order->total() + $order->shippingOption->price, $order->currency_code, app()->getLocale()) }}
                        </dd>
                    </div>
                </dl>
                <div class="mt-6 space-y-4 sm:flex sm:space-x-4 sm:space-y-0 md:mt-0">
                    <x-buttons.default :href="route('account.orders.detail', [
                        'locale' => app()->getLocale(),
                        'number' => $order->number,
                    ])" class="flex w-full px-4 py-2 text-sm md:w-auto">
                        {{ __('Detail') }}
                        <span class="sr-only">{{ $order->number }}</span>
                    </x-buttons.default>
                    <x-buttons.default class="flex w-full px-4 py-2 text-sm md:w-auto">
                        {{ __('View invoice') }}
                        <span class="sr-only">
                            {{ __('For the order :number', ['number' => $order->number]) }}
                        </span>
                    </x-buttons.default>
                </div>
            </div>
        </div>
        <div class="lg:grid lg:grid-cols-2 lg:gap-x-12">
            <div class="space-y-10 ">
                <div class="flow-root px-4 sm:px-0">
                    <div class="-my-6 divide-y divide-gray-200 sm:-my-10">
                        @foreach ($order->items as $item)
                            <x-order.item :item="$item" :currency_code="$order->currency_code" />
                        @endforeach
                    </div>
                </div>
                <div class="bg-gray-50 p-4">
                    <div class="flex">
                        <div class="shrink-0">
                            <x-untitledui-info-circle class="size-5 text-gray-400" stroke-width="1.5"
                                                      aria-hidden="true" />
                        </div>
                        <div class="ml-3 flex-1 md:flex md:justify-between">
                            <p class="text-sm text-gray-700">
                                {{ __('You can follow the progress and processing of your order from your profile.') }}
                            </p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm pl-8">
                        <x-link :href="route('account.orders', ['locale' => app()->getLocale()])"
                                class="whitespace-nowrap font-medium text-gray-700 hover:text-gray-600">
                            {{ __('My orders') }}
                            <span aria-hidden="true"> &rarr;</span>
                        </x-link>
                    </p>
                </div>
            </div>
            <div>
                <div class="flex items-end justify-end">
                    <h6 class="bg-primary-500 inline-flex w-auto px-2.5 py-1 text-sm leading-6 text-white">
                        {{ __('Order summary') }}
                    </h6>
                </div>
                <x-order.summary :order="$order" />
            </div>
        </div>
    </div>
</x-container>
