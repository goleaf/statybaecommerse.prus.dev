<div class="text-right">
	<div class="space-y-2">
		<div class="flex items-center justify-between">
			<span class="text-gray-300">{{ __('Subtotal') }}</span>
			<span>{{ \Illuminate\Support\Number::currency($subtotal, current_currency(), app()->getLocale()) }}</span>
		</div>
		@if($discount > 0)
			<div class="flex items-center justify-between">
				<span class="text-gray-300">{{ __('Discount') }}</span>
				<span>-{{ \Illuminate\Support\Number::currency($discount, current_currency(), app()->getLocale()) }}</span>
			</div>
		@endif
		@if(session()->has('checkout.coupon.code'))
			<div class="flex items-center justify-between text-xs text-gray-400">
				<span>{{ __('Coupon') }}</span>
				<span>{{ session('checkout.coupon.code') }}</span>
			</div>
		@endif
		<div class="flex items-center justify-between text-base font-semibold border-t border-white/10 pt-3">
			<span>{{ __('Total') }}</span>
			<span>{{ \Illuminate\Support\Number::currency($total, current_currency(), app()->getLocale()) }}</span>
		</div>
	</div>
</div>
