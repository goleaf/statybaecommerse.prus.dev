<div class="space-y-3">
    <div class="flex items-center justify-between border-b border-gray-200 pb-3 text-sm">
        <span class="text-gray-600">{{ __('messages.subtotal') }}</span>
        <span class="font-semibold text-gray-900">
            {{ \Illuminate\Support\Number::currency($subtotal, current_currency(), app()->getLocale()) }}
        </span>
    </div>

    @if($discount > 0)
        <div class="flex items-center justify-between border-b border-gray-200 pb-3 text-sm">
            <span class="text-gray-600">{{ __('messages.discount') }}</span>
            <span class="font-semibold text-green-700">
                -{{ \Illuminate\Support\Number::currency($discount, current_currency(), app()->getLocale()) }}
            </span>
        </div>
    @endif

    @if(session()->has('checkout.coupon.code'))
        <div class="flex items-center justify-between text-xs">
            <span class="text-gray-500">{{ __('translations.coupon_applied') }}</span>
            <span class="font-semibold uppercase tracking-wide text-gray-700">{{ session('checkout.coupon.code') }}</span>
        </div>
    @endif

    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-3">
        <span class="text-sm font-semibold text-gray-900">{{ __('messages.total') }}</span>
        <span class="text-xl font-bold text-brand-primary">
            {{ \Illuminate\Support\Number::currency($total, current_currency(), app()->getLocale()) }}
        </span>
    </div>

    <div class="space-y-1 text-xs text-gray-500">
        <p>{{ __('translations.prices_include_vat') }}</p>
        <p>{{ __('translations.free_shipping_on_orders_over') }} {{ \Illuminate\Support\Number::currency(100, current_currency(), app()->getLocale()) }}</p>
    </div>
</div>
