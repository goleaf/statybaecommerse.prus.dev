<section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <header class="space-y-1">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('Billing details') }}</h2>
        <p class="text-sm text-gray-500">
            {{ __('We use these details for your invoice and to send order updates.') }}
        </p>
    </header>

    <form wire:submit.prevent="toStep(2)" class="mt-6 space-y-8">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="billing_first_name" class="block text-sm font-medium text-gray-700">
                    {{ __('First name') }}
                </label>
                <input
                    id="billing_first_name"
                    type="text"
                    wire:model.debounce.500ms="billing.first_name"
                    autocomplete="given-name"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                @error('billing.first_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_last_name" class="block text-sm font-medium text-gray-700">
                    {{ __('Last name') }}
                </label>
                <input
                    id="billing_last_name"
                    type="text"
                    wire:model.debounce.500ms="billing.last_name"
                    autocomplete="family-name"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                @error('billing.last_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_email" class="block text-sm font-medium text-gray-700">
                    {{ __('Email address') }}
                </label>
                <input
                    id="billing_email"
                    type="email"
                    wire:model.debounce.500ms="billing.email"
                    autocomplete="email"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                @error('billing.email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_phone" class="block text-sm font-medium text-gray-700">
                    {{ __('Phone number') }}
                </label>
                <input
                    id="billing_phone"
                    type="tel"
                    wire:model.debounce.500ms="billing.phone"
                    autocomplete="tel"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                @error('billing.phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="billing_company" class="block text-sm font-medium text-gray-700">
                    {{ __('Company (optional)') }}
                </label>
                <input
                    id="billing_company"
                    type="text"
                    wire:model.debounce.500ms="billing.company"
                    autocomplete="organization"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                @error('billing.company')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="billing_address" class="block text-sm font-medium text-gray-700">
                    {{ __('Street address') }}
                </label>
                <input
                    id="billing_address"
                    type="text"
                    wire:model.debounce.500ms="billing.address"
                    autocomplete="address-line1"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                @error('billing.address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_city" class="block text-sm font-medium text-gray-700">
                    {{ __('City') }}
                </label>
                <input
                    id="billing_city"
                    type="text"
                    wire:model.debounce.500ms="billing.city"
                    autocomplete="address-level2"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                @error('billing.city')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_region" class="block text-sm font-medium text-gray-700">
                    {{ __('Region / state (optional)') }}
                </label>
                <input
                    id="billing_region"
                    type="text"
                    wire:model.debounce.500ms="billing.region"
                    autocomplete="address-level1"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                @error('billing.region')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_postal_code" class="block text-sm font-medium text-gray-700">
                    {{ __('Postal code') }}
                </label>
                <input
                    id="billing_postal_code"
                    type="text"
                    wire:model.debounce.500ms="billing.postal_code"
                    autocomplete="postal-code"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                @error('billing.postal_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="billing_country" class="block text-sm font-medium text-gray-700">
                    {{ __('Country') }}
                </label>
                <select
                    id="billing_country"
                    wire:model="billing.country"
                    autocomplete="country"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
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
                        {{ __('Ship to the billing address') }}
                    </span>
                    <span class="block text-xs text-gray-500">
                        {{ __('Uncheck to enter a different shipping address.') }}
                    </span>
                </span>
            </label>
        </div>

        @if (! $sameAsShipping)
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('Shipping address') }}</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_first_name" class="block text-sm font-medium text-gray-700">
                            {{ __('First name') }}
                        </label>
                        <input
                            id="shipping_first_name"
                            type="text"
                            wire:model.debounce.500ms="shipping.first_name"
                            autocomplete="shipping given-name"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                        @error('shipping.first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_last_name" class="block text-sm font-medium text-gray-700">
                            {{ __('Last name') }}
                        </label>
                        <input
                            id="shipping_last_name"
                            type="text"
                            wire:model.debounce.500ms="shipping.last_name"
                            autocomplete="shipping family-name"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                        @error('shipping.last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="shipping_company" class="block text-sm font-medium text-gray-700">
                            {{ __('Company (optional)') }}
                        </label>
                        <input
                            id="shipping_company"
                            type="text"
                            wire:model.debounce.500ms="shipping.company"
                            autocomplete="shipping organization"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                        @error('shipping.company')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="shipping_address" class="block text-sm font-medium text-gray-700">
                            {{ __('Street address') }}
                        </label>
                        <input
                            id="shipping_address"
                            type="text"
                            wire:model.debounce.500ms="shipping.address"
                            autocomplete="shipping address-line1"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                        @error('shipping.address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_city" class="block text-sm font-medium text-gray-700">
                            {{ __('City') }}
                        </label>
                        <input
                            id="shipping_city"
                            type="text"
                            wire:model.debounce.500ms="shipping.city"
                            autocomplete="shipping address-level2"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                        @error('shipping.city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_region" class="block text-sm font-medium text-gray-700">
                            {{ __('Region / state (optional)') }}
                        </label>
                        <input
                            id="shipping_region"
                            type="text"
                            wire:model.debounce.500ms="shipping.region"
                            autocomplete="shipping address-level1"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                        @error('shipping.region')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700">
                            {{ __('Postal code') }}
                        </label>
                        <input
                            id="shipping_postal_code"
                            type="text"
                            wire:model.debounce.500ms="shipping.postal_code"
                            autocomplete="shipping postal-code"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                        @error('shipping.postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="shipping_country" class="block text-sm font-medium text-gray-700">
                            {{ __('Country') }}
                        </label>
                        <select
                            id="shipping_country"
                            wire:model="shipping.country"
                            autocomplete="shipping country"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
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

        <div class="flex items-center justify-end">
            <x-buttons.primary
                type="submit"
                class="px-6 py-2 text-sm"
                wire:loading.attr="disabled"
                wire:target="toStep"
            >
                {{ __('Continue to shipping') }}
            </x-buttons.primary>
        </div>
    </form>
</section>
