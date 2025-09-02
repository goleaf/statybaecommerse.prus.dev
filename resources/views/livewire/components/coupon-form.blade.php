<div class="mt-6">
    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Coupon code') }}</label>
    <div class="mt-2 flex gap-2">
        <input type="text" wire:model.defer="code" class="flex-1 border-gray-300" placeholder="{{ __('Enter code') }}" />
        <x-buttons.primary wire:click="apply">{{ __('Apply') }}</x-buttons.primary>
        @if (session()->has('checkout.coupon'))
            <button type="button" wire:click="remove"
                    class="inline-flex items-center rounded-md px-3 py-2 text-sm border border-gray-300 text-gray-700 hover:bg-gray-50">
                {{ __('Remove') }}
            </button>
        @endif
    </div>
</div>
