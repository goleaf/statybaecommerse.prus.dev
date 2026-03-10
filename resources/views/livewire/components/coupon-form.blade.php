<div class="space-y-3">
    <div class="flex gap-2">
        <input type="text"
               wire:model.defer="code"
               placeholder="{{ __('translations.enter_coupon_code') }}"
               class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/25" />
        <button type="button"
                wire:click="apply"
                wire:loading.attr="disabled"
                class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg bg-brand-primary px-4 text-sm font-semibold text-white transition disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove>{{ __('messages.apply') }}</span>
            <span wire:loading>{{ __('translations.applying') }}</span>
        </button>
    </div>

    @error('code')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    @if (session()->has('checkout.coupon'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-3 py-2">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm text-green-800">
                    {{ __('translations.coupon_applied') }}:
                    <span class="font-semibold">{{ session('checkout.coupon.code') }}</span>
                </p>
                <button type="button"
                        wire:click="remove"
                        wire:confirm="{{ __('translations.confirm_remove_coupon') }}"
                        wire:loading.attr="disabled"
                        class="cart-button-text-danger text-sm font-medium text-red-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ __('messages.remove') }}
                </button>
            </div>
        </div>
    @endif
</div>
