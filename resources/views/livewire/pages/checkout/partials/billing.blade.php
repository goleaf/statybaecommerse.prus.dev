<section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <header class="space-y-1">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('ui.billing_details') }}</h2>
        <p class="text-sm text-gray-500">
            {{ __('ui.we_use_these_details_for_your_invoice_and_to_send_order_updates') }}
        </p>
    </header>

    <form wire:submit.prevent="toStep(2)" class="mt-6 space-y-8">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="billing_first_name" class="block text-sm font-medium text-gray-700">
                    {{ __('ui.first_name') }}
                </label>
                <input
                    id="billing_first_name"
                    type="text"
                    wire:model.debounce.500ms="billing.first_name"
                    autocomplete="given-name"
                    class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                @error('billing.first_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_last_name" class="block text-sm font-medium text-gray-700">
                    {{ __('ui.last_name') }}
                </label>
                <input
                    id="billing_last_name"
                    type="text"
                    wire:model.debounce.500ms="billing.last_name"
                    autocomplete="family-name"
                    class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                @error('billing.last_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_email" class="block text-sm font-medium text-gray-700">
                    {{ __('ui.email_address') }}
                </label>
                <input
                    id="billing_email"
                    type="email"
                    wire:model.debounce.500ms="billing.email"
                    autocomplete="email"
                    class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                @error('billing.email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_phone" class="block text-sm font-medium text-gray-700">
                    {{ __('ui.phone_number') }}
                </label>
                <input
                    id="billing_phone"
                    type="tel"
                    wire:model.debounce.500ms="billing.phone"
                    autocomplete="tel"
                    class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                @error('billing.phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="billing_company" class="block text-sm font-medium text-gray-700">
                    {{ __('ui.company_optional') }}
                </label>
                <input
                    id="billing_company"
                    type="text"
                    wire:model.debounce.500ms="billing.company"
                    autocomplete="organization"
                    class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                @error('billing.company')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="billing_address" class="block text-sm font-medium text-gray-700">
                    {{ __('ui.street_address') }}
                </label>
                <input
                    id="billing_address"
                    type="text"
                    wire:model.debounce.500ms="billing.address"
                    autocomplete="address-line1"
                    class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                @error('billing.address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_city" class="block text-sm font-medium text-gray-700">
                    {{ __('ui.city') }}
                </label>
                <input
                    id="billing_city"
                    type="text"
                    wire:model.debounce.500ms="billing.city"
                    autocomplete="address-level2"
                    class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                @error('billing.city')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_region" class="block text-sm font-medium text-gray-700">
                    {{ __('ui.region_state_optional') }}
                </label>
                <input
                    id="billing_region"
                    type="text"
                    wire:model.debounce.500ms="billing.region"
                    autocomplete="address-level1"
                    class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                @error('billing.region')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_postal_code" class="block text-sm font-medium text-gray-700">
                    {{ __('ui.postal_code') }}
                </label>
                <input
                    id="billing_postal_code"
                    type="text"
                    wire:model.debounce.500ms="billing.postal_code"
                    autocomplete="postal-code"
                    class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                @error('billing.postal_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_country" class="block text-sm font-medium text-gray-700">
                    {{ __('ui.country') }}
                </label>
                <select
                    id="billing_country"
                    wire:model.debounce.500ms="billing.country"
                    autocomplete="country"
                    class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                    @foreach ($countries as $country)
                        <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                    @endforeach
                </select>
                @error('billing.country')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <label class="flex items-start gap-3">
                <input
                    type="checkbox"
                    wire:model.live="sameAsShipping"
                    class="mt-1 size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600"
                >
                <span>
                    <span class="block text-sm font-medium text-gray-900">
                        {{ __('ui.ship_to_the_billing_address') }}
                    </span>
                    <span class="block text-xs text-gray-500">
                        {{ __('ui.uncheck_to_enter_a_different_shipping_address') }}
                    </span>
                </span>
            </label>
        </div>

        @if (! $sameAsShipping)
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('ui.shipping_address') }}</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_first_name" class="block text-sm font-medium text-gray-700">
                            {{ __('ui.first_name') }}
                        </label>
                        <input
                            id="shipping_first_name"
                            type="text"
                            wire:model.debounce.500ms="shipping.first_name"
                            autocomplete="shipping given-name"
                            class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                        >
                        @error('shipping.first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_last_name" class="block text-sm font-medium text-gray-700">
                            {{ __('ui.last_name') }}
                        </label>
                        <input
                            id="shipping_last_name"
                            type="text"
                            wire:model.debounce.500ms="shipping.last_name"
                            autocomplete="shipping family-name"
                            class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                        >
                        @error('shipping.last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="shipping_company" class="block text-sm font-medium text-gray-700">
                            {{ __('ui.company_optional') }}
                        </label>
                        <input
                            id="shipping_company"
                            type="text"
                            wire:model.debounce.500ms="shipping.company"
                            autocomplete="shipping organization"
                            class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                        >
                        @error('shipping.company')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="shipping_address" class="block text-sm font-medium text-gray-700">
                            {{ __('ui.street_address') }}
                        </label>
                        <input
                            id="shipping_address"
                            type="text"
                            wire:model.debounce.500ms="shipping.address"
                            autocomplete="shipping address-line1"
                            class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                        >
                        @error('shipping.address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_city" class="block text-sm font-medium text-gray-700">
                            {{ __('ui.city') }}
                        </label>
                        <input
                            id="shipping_city"
                            type="text"
                            wire:model.debounce.500ms="shipping.city"
                            autocomplete="shipping address-level2"
                            class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                        >
                        @error('shipping.city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_region" class="block text-sm font-medium text-gray-700">
                            {{ __('ui.region_state_optional') }}
                        </label>
                        <input
                            id="shipping_region"
                            type="text"
                            wire:model.debounce.500ms="shipping.region"
                            autocomplete="shipping address-level1"
                            class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                        >
                        @error('shipping.region')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700">
                            {{ __('ui.postal_code') }}
                        </label>
                        <input
                            id="shipping_postal_code"
                            type="text"
                            wire:model.debounce.500ms="shipping.postal_code"
                            autocomplete="shipping postal-code"
                            class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                        >
                        @error('shipping.postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_country" class="block text-sm font-medium text-gray-700">
                            {{ __('ui.country') }}
                        </label>
                        <select
                            id="shipping_country"
                            wire:model.debounce.500ms="shipping.country"
                            autocomplete="shipping country"
                            class="mt-1 block h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                        >
                            @foreach ($countries as $country)
                                <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                        @error('shipping.country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        @endif

        <div
            wire:loading.flex
            wire:target="resolveShippingOptions"
            class="mt-4 flex flex-col gap-2 rounded-lg border border-dashed border-primary-200 bg-primary-50 p-4 text-sm text-primary-700 animate-pulse"
            role="status"
        >
            <div class="h-3 w-2/3 rounded bg-primary-200/60"></div>
            <div class="h-3 w-1/2 rounded bg-primary-200/40"></div>
        </div>

        <div class="flex items-center justify-end">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                wire:target="toStep,resolveShippingOptions"
            >
                {{ __('ui.continue_to_shipping') }}
            </button>
        </div>
    </form>
</section>
