@props(['order'])

@php
    $currency = (string) ($order->currency ?? 'EUR');
    $subtotal = (float) ($order->subtotal ?? 0);
    $shippingAmount = (float) ($order->shipping_amount ?? $order->shippingOption?->price ?? 0);
    $taxAmount = (float) ($order->tax_amount ?? 0);
    $totalAmount = (float) ($order->total ?? ($subtotal + $shippingAmount + $taxAmount));
    $paymentMethod = $order->payment_method;
    $paymentMethodLabel = $paymentMethod instanceof \App\Enums\PaymentMethod
        ? (string) $paymentMethod->getLabel()
        : __('frontend.order_summary.not_available');
@endphp

<div class="divide-y divide-dashed divide-gray-200 border border-gray-200 py-4">
    <dl class="space-y-4 px-4 pb-4">
        <div class="flex items-center justify-between">
            <dt class="text-sm">{{ __('frontend.order_summary.subtotal') }}</dt>
            <dd class="text-sm font-medium text-gray-900">
                {{ \Illuminate\Support\Number::currency($subtotal, $currency, app()->getLocale()) }}
            </dd>
        </div>
        <div class="flex items-center justify-between">
            <dt class="text-sm">{{ __('frontend.order_summary.shipping') }}</dt>
            <dd class="text-sm font-medium text-gray-900">
                {{ \Illuminate\Support\Number::currency($shippingAmount, $currency, app()->getLocale()) }}
            </dd>
        </div>
        <div class="flex items-center justify-between">
            <dt class="text-sm">{{ __('frontend.order_summary.tax') }}</dt>
            <dd class="text-sm font-medium text-gray-900">
                {{ \Illuminate\Support\Number::currency($taxAmount, $currency, app()->getLocale()) }}
            </dd>
        </div>
        <div class="flex items-center justify-between border-t border-gray-200 pt-4">
            <dt class="font-heading font-medium text-gray-900">{{ __('frontend.order_summary.total') }}</dt>
            <dd class="text-base font-bold text-gray-900">
                {{ \Illuminate\Support\Number::currency($totalAmount, $currency, app()->getLocale()) }}
            </dd>
        </div>
    </dl>
    <dl class="grid grid-cols-2 gap-x-6 p-4 text-sm">
        <div>
            <dt class="font-medium text-gray-900">{{ __('frontend.order_summary.shipping_address') }}</dt>
            @php($sa = is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address)
            <dd class="mt-2 text-gray-500">
                @if ($sa)
                    <span class="block">{{ $sa['first_name'] ?? '' }} {{ $sa['last_name'] ?? '' }}</span>
                    <span class="block">{{ $sa['street_address'] ?? ($sa['address_line1'] ?? ($sa['address'] ?? '')) }}</span>
                    @if (!empty($sa['street_address_plus'] ?? ($sa['address_line2'] ?? null)))
                        <span class="block">{{ $sa['street_address_plus'] ?? $sa['address_line2'] }}</span>
                    @endif
                    <span
                          class="block">{{ $sa['city'] ?? '' }}{{ !empty($sa['city']) && !empty($sa['postal_code']) ? ', ' : '' }}{{ $sa['postal_code'] ?? '' }}</span>
                    <span
                          class="block">{{ $sa['country_name'] ?? ($sa['country'] ?? ($sa['country_code'] ?? '')) }}</span>
                @else
                    <span class="block">{{ __('frontend.order_summary.not_available') }}</span>
                @endif
            </dd>
        </div>
        <div>
            <dt class="font-medium text-gray-900">{{ __('frontend.order_summary.billing_address') }}</dt>
            @php($ba = is_string($order->billing_address) ? json_decode($order->billing_address, true) : $order->billing_address)
            <dd class="mt-2 text-gray-500">
                @if ($ba)
                    <span class="block">{{ $ba['first_name'] ?? '' }} {{ $ba['last_name'] ?? '' }}</span>
                    <span class="block">{{ $ba['street_address'] ?? ($ba['address_line1'] ?? ($ba['address'] ?? '')) }}</span>
                    @if (!empty($ba['street_address_plus'] ?? ($ba['address_line2'] ?? null)))
                        <span class="block">{{ $ba['street_address_plus'] ?? $ba['address_line2'] }}</span>
                    @endif
                    <span
                          class="block">{{ $ba['city'] ?? '' }}{{ !empty($ba['city']) && !empty($ba['postal_code']) ? ', ' : '' }}{{ $ba['postal_code'] ?? '' }}</span>
                    <span
                          class="block">{{ $ba['country_name'] ?? ($ba['country'] ?? ($ba['country_code'] ?? '')) }}</span>
                @else
                    <span class="block">{{ __('frontend.order_summary.not_available') }}</span>
                @endif
            </dd>
        </div>
    </dl>
    <dl class="space-y-3 p-4">
        <dt class="text-sm leading-6 font-medium text-gray-900">
            {{ __('frontend.order_summary.payment_method') }}
        </dt>
        <dd class="text-sm flex items-center gap-2 text-gray-500">
            <span class="font-medium text-base leading-6">
                {{ $paymentMethodLabel }}
            </span>
        </dd>
    </dl>
</div>
