<div class="text-right">
	<div class="space-y-2">
		<div class="flex items-center justify-between">
			<span class="text-gray-300">{{ __('Subtotal') }}</span>
			<span>{{ shopper_money_format(amount: $subtotal, currency: current_currency()) }}</span>
		</div>
		@if($discount > 0)
			<div class="flex items-center justify-between">
				<span class="text-gray-300">{{ __('Discount') }}</span>
				<span>-{{ shopper_money_format(amount: $discount, currency: current_currency()) }}</span>
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
			<span>{{ shopper_money_format(amount: $total, currency: current_currency()) }}</span>
		</div>
	</div>
</div>
