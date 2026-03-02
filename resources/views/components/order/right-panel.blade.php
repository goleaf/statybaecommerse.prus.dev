@props([
    'items' => [],
    'summary' => [],
    'itemCount' => null,
    'showCoupon' => false,
    'summaryPanelClass' => '',
])

@if ($showCoupon)
    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('translations.coupon_code') }}</h2>
        <div class="mt-5">
            <livewire:components.coupon-form />
        </div>
    </section>
@endif

<x-order.summary-panel
    :items="$items"
    :summary="$summary"
    :item-count="$itemCount"
    :class="$summaryPanelClass"
/>

<p class="text-center text-xs text-gray-500">{{ __('translations.secure_checkout_encrypted_payments') }}</p>
