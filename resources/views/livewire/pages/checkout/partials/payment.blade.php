<section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <header class="space-y-1">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('Payment') }}</h2>
        <p class="text-sm text-gray-500">
            {{ __('Choose how you would like to pay and add any notes for our team before placing your order.') }}
        </p>
    </header>

    <form wire:submit.prevent="placeOrder" class="mt-6 space-y-8">
        <fieldset class="space-y-3" aria-label="{{ __('Payment methods') }}">
            <legend class="text-sm font-semibold text-gray-900">{{ __('Payment method') }}</legend>
            @foreach ($methods as $method => $label)
                <label
                    @class([
                        'flex items-center justify-between gap-4 rounded-lg border p-4 transition',
                        'border-primary-200 bg-primary-50 shadow-sm' => $selectedPaymentMethod === $method,
                        'border-gray-200 hover:border-primary-200 hover:bg-primary-50/50' => $selectedPaymentMethod !== $method,
                    ])
                >
                    <span class="flex items-center gap-3">
                        <input
                            type="radio"
                            value="{{ $method }}"
                            wire:model="selectedPaymentMethod"
                            class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600"
                        >
                        <span class="text-sm font-medium text-gray-900">{{ $label }}</span>
                    </span>
                </label>
            @endforeach
        </fieldset>

        @error('selectedPaymentMethod')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div>
            <label for="order_notes" class="block text-sm font-medium text-gray-700">
                {{ __('Order notes (optional)') }}
            </label>
            <textarea
                id="order_notes"
                rows="4"
                wire:model.defer="notes"
                class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
            ></textarea>
            @error('notes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-xs leading-5 text-gray-500">
            {{ __("By placing your order you agree to our terms of sale, returns policy, and confirm that your contact details are correct for delivery updates.") }}
        </p>

        <div class="flex flex-wrap justify-between gap-3 pt-2">
            <x-buttons.secondary type="button" wire:click="toStep(2)">
                {{ __('Back to shipping') }}
            </x-buttons.secondary>
            <x-buttons.primary
                type="submit"
                class="px-6 py-2 text-sm"
                wire:loading.attr="disabled"
                wire:target="resolveShippingOptions,placeOrder"
            >
                <span class="flex items-center gap-2">
                    <span wire:loading.flex wire:target="placeOrder">
                        <x-loading-dots class="text-white" aria-hidden="true" />
                    </span>
                    {{ __('Place order') }}
                </span>
            </x-buttons.primary>
        </div>
    </form>
</section>
