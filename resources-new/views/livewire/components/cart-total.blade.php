<div class="space-y-4">
    <!-- Subtotal -->
    <div class="flex items-center justify-between py-3 border-b border-ash/20">
        <span class="text-gray-600 font-medium">{{ __('Subtotal') }}</span>
        <span class="text-lg font-semibold text-dark">
            {{ \Illuminate\Support\Number::currency($subtotal, current_currency(), app()->getLocale()) }}
        </span>
    </div>

    <!-- Discount -->
    @if($discount > 0)
        <div class="flex items-center justify-between py-3 border-b border-ash/20">
            <span class="text-gray-600 font-medium">{{ __('Discount') }}</span>
            <span class="text-lg font-semibold text-green-600">
                -{{ \Illuminate\Support\Number::currency($discount, current_currency(), app()->getLocale()) }}
            </span>
        </div>
    @endif

    <!-- Coupon Code Display -->
    @if(session()->has('checkout.coupon.code'))
        <div class="flex items-center justify-between py-2 text-sm">
            <span class="text-gray-500">{{ __('Coupon Applied') }}</span>
            <span class="text-brand-primary font-medium">{{ session('checkout.coupon.code') }}</span>
        </div>
    @endif

    <!-- Total -->
    <div class="flex items-center justify-between py-4 bg-brand-primary/5 rounded-xl px-4">
        <span class="text-xl font-bold text-dark">{{ __('Total') }}</span>
        <span class="text-2xl font-bold text-brand-primary">
            {{ \Illuminate\Support\Number::currency($total, current_currency(), app()->getLocale()) }}
        </span>
    </div>

    <!-- Additional Info -->
    <div class="text-center text-sm text-gray-500">
        <p>{{ __('Prices include VAT') }}</p>
        <p>{{ __('Free shipping on orders over') }} {{ \Illuminate\Support\Number::currency(100, current_currency(), app()->getLocale()) }}</p>
    </div>
</div>