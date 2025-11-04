<div class="space-y-4">
    <div class="space-y-2">
        <label class="block text-sm font-medium text-dark">{{ __('Coupon Code') }}</label>
        <div class="flex gap-2">
            <div class="flex-1 relative">
                <input type="text" 
                       wire:model.defer="code" 
                       placeholder="{{ __('Enter coupon code') }}" 
                       class="w-full px-4 py-3 border border-ash rounded-xl bg-white/80 text-dark placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all duration-200" />
                @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="button" 
                    wire:click="apply"
                    class="px-6 py-3 bg-brand-primary text-white rounded-xl font-medium hover:bg-brand-primary/90 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>{{ __('Apply') }}</span>
                <span wire:loading>{{ __('Applying...') }}</span>
            </button>
        </div>
    </div>

    @if (session()->has('checkout.coupon'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-sm font-medium text-green-800">{{ __('Coupon Applied') }}</span>
                </div>
                <button type="button" 
                        wire:click="remove"
                        wire:confirm="{{ __('translations.confirm_remove_coupon') }}"
                        class="text-sm text-red-600 hover:text-red-700 hover:underline transition-colors">
                    {{ __('Remove') }}
                </button>
            </div>
        </div>
    @endif

    @if (session()->has('coupon_error'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="text-sm font-medium text-red-800">{{ session('coupon_error') }}</span>
            </div>
        </div>
    @endif
</div>