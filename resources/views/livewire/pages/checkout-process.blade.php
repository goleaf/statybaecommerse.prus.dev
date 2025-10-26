{{--
    Checkout process multi-step form wrapper with Livewire bindings.
    This template renders billing, shipping, and payment steps while displaying
    a persistent order summary so the shopper sees totals update in real time.
--}}
<div class="bg-white py-10">
    <x-container class="grid gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        {{-- Main wizard column with the individual checkout steps. --}}
        <section class="space-y-8">
            {{-- Step indicator keeps the shopper oriented through the process. --}}
            @php($steps = [
                ['label' => __('Addresses'), 'number' => 1],
                ['label' => __('Delivery'), 'number' => 2],
                ['label' => __('Payment'), 'number' => 3],
                ['label' => __('Review'), 'number' => 4],
            ])

            <ol class="flex flex-wrap items-center gap-4 text-sm font-semibold">
                @foreach ($steps as $step)
                    @php
                        $isActive = $currentStep === $step['number'];
                        $isComplete = $currentStep > $step['number'];
                    @endphp
                    <li class="flex items-center gap-2">
                        <span
                            class="flex size-8 items-center justify-center rounded-full border text-xs"
                            @class([
                                'border-primary-600 bg-primary-600 text-white' => $isActive,
                                'border-primary-600 bg-primary-100 text-primary-700' => $isComplete && ! $isActive,
                                'border-gray-300 bg-white text-gray-600' => ! $isActive && ! $isComplete,
                            ])
                        >
                            {{ $step['number'] }}
                        </span>
                        <span @class(['text-primary-700' => $isActive, 'text-gray-500' => ! $isActive])>
                            {{ $step['label'] }}
                        </span>
                        @if (! $loop->last)
                            <span class="text-gray-300">&rsaquo;</span>
                        @endif
                    </li>
                @endforeach
            </ol>

            {{-- Address step collects billing and optional shipping contacts. --}}
            @if ($currentStep === 1)
                <form wire:submit.prevent="nextStep" class="space-y-8">
                    <div class="grid gap-6 sm:grid-cols-2">
                        {{-- Individual billing field with Livewire bindings. --}}
                        <div class="space-y-2">
                            <label for="billing-first-name" class="text-sm font-medium text-gray-700">
                                {{ __('First name') }}
                            </label>
                            <input
                                wire:model.defer="billingFirstName"
                                id="billing-first-name"
                                type="text"
                                autocomplete="given-name"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('billingFirstName')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="billing-last-name" class="text-sm font-medium text-gray-700">
                                {{ __('Last name') }}
                            </label>
                            <input
                                wire:model.defer="billingLastName"
                                id="billing-last-name"
                                type="text"
                                autocomplete="family-name"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('billingLastName')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="billing-email" class="text-sm font-medium text-gray-700">
                                {{ __('Email address') }}
                            </label>
                            <input
                                wire:model.defer="billingEmail"
                                id="billing-email"
                                type="email"
                                autocomplete="email"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('billingEmail')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="billing-phone" class="text-sm font-medium text-gray-700">
                                {{ __('Phone number') }}
                            </label>
                            <input
                                wire:model.defer="billingPhone"
                                id="billing-phone"
                                type="tel"
                                autocomplete="tel"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('billingPhone')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="space-y-2 sm:col-span-2">
                            <label for="billing-company" class="text-sm font-medium text-gray-700">
                                {{ __('Company (optional)') }}
                            </label>
                            <input
                                wire:model.defer="billingCompany"
                                id="billing-company"
                                type="text"
                                autocomplete="organization"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('billingCompany')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <label for="billing-address" class="text-sm font-medium text-gray-700">
                                {{ __('Street address') }}
                            </label>
                            <input
                                wire:model.defer="billingAddress"
                                id="billing-address"
                                type="text"
                                autocomplete="street-address"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('billingAddress')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="billing-city" class="text-sm font-medium text-gray-700">
                                {{ __('City') }}
                            </label>
                            <input
                                wire:model.defer="billingCity"
                                id="billing-city"
                                type="text"
                                autocomplete="address-level2"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('billingCity')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="billing-postal" class="text-sm font-medium text-gray-700">
                                {{ __('Postal code') }}
                            </label>
                            <input
                                wire:model.defer="billingPostalCode"
                                id="billing-postal"
                                type="text"
                                autocomplete="postal-code"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('billingPostalCode')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="billing-country" class="text-sm font-medium text-gray-700">
                                {{ __('Country code') }}
                            </label>
                            <input
                                wire:model.defer="billingCountryCode"
                                id="billing-country"
                                type="text"
                                maxlength="2"
                                autocomplete="country"
                                class="w-full uppercase rounded-md border border-gray-300 px-3 py-2 text-sm tracking-wider shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                            >
                            @error('billingCountryCode')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-6">
                        {{-- Toggle that lets the shopper reuse billing details for shipping. --}}
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
                            <label class="flex items-center gap-3 text-sm font-medium text-gray-700">
                                <input
                                    wire:model="sameAsShipping"
                                    type="checkbox"
                                    class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600"
                                >
                                {{ __('Shipping address is the same as billing') }}
                            </label>
                            <p class="mt-2 text-xs text-gray-500">
                                {{ __('Uncheck if you need to ship items to a different person or location.') }}
                            </p>
                        </div>

                        @unless ($sameAsShipping)
                            <div class="grid gap-6 sm:grid-cols-2">
                                {{-- Dedicated shipping contact fields when the address differs. --}}
                                <div class="space-y-2">
                                    <label for="shipping-first-name" class="text-sm font-medium text-gray-700">
                                        {{ __('Recipient first name') }}
                                    </label>
                                    <input
                                        wire:model.defer="shippingFirstName"
                                        id="shipping-first-name"
                                        type="text"
                                        autocomplete="given-name"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                                    >
                                    @error('shippingFirstName')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="shipping-last-name" class="text-sm font-medium text-gray-700">
                                        {{ __('Recipient last name') }}
                                    </label>
                                    <input
                                        wire:model.defer="shippingLastName"
                                        id="shipping-last-name"
                                        type="text"
                                        autocomplete="family-name"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                                    >
                                    @error('shippingLastName')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2 sm:col-span-2">
                                    <label for="shipping-company" class="text-sm font-medium text-gray-700">
                                        {{ __('Company (optional)') }}
                                    </label>
                                    <input
                                        wire:model.defer="shippingCompany"
                                        id="shipping-company"
                                        type="text"
                                        autocomplete="organization"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                                    >
                                    @error('shippingCompany')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2 sm:col-span-2">
                                    <label for="shipping-address" class="text-sm font-medium text-gray-700">
                                        {{ __('Street address') }}
                                    </label>
                                    <input
                                        wire:model.defer="shippingAddress"
                                        id="shipping-address"
                                        type="text"
                                        autocomplete="street-address"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                                    >
                                    @error('shippingAddress')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="shipping-city" class="text-sm font-medium text-gray-700">
                                        {{ __('City') }}
                                    </label>
                                    <input
                                        wire:model.defer="shippingCity"
                                        id="shipping-city"
                                        type="text"
                                        autocomplete="address-level2"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                                    >
                                    @error('shippingCity')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="shipping-postal" class="text-sm font-medium text-gray-700">
                                        {{ __('Postal code') }}
                                    </label>
                                    <input
                                        wire:model.defer="shippingPostalCode"
                                        id="shipping-postal"
                                        type="text"
                                        autocomplete="postal-code"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                                    >
                                    @error('shippingPostalCode')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="shipping-country" class="text-sm font-medium text-gray-700">
                                        {{ __('Country code') }}
                                    </label>
                                    <input
                                        wire:model.defer="shippingCountryCode"
                                        id="shipping-country"
                                        type="text"
                                        maxlength="2"
                                        autocomplete="country"
                                        class="w-full uppercase rounded-md border border-gray-300 px-3 py-2 text-sm tracking-wider shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                                    >
                                    @error('shippingCountryCode')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endunless
                    </div>

                    {{-- Navigation controls keep the customer moving to the next step. --}}
                    <div class="flex justify-end">
                        <x-button type="submit" class="min-w-32 justify-center">
                            {{ __('Continue to delivery') }}
                        </x-button>
                    </div>
                </form>
            @endif

            {{-- Delivery step shows carrier choices and special instructions. --}}
            @if ($currentStep === 2)
                <form wire:submit.prevent="nextStep" class="space-y-6">
                    <div class="space-y-4">
                        {{-- Shipping options radio list rendered from the resolver data. --}}
                        <h3 class="text-sm font-semibold text-gray-700">{{ __('Select a shipping method') }}</h3>

                        @if ($availableShippingOptions === [])
                            <p class="rounded-md border border-dashed border-red-200 bg-red-50 p-4 text-sm text-red-700">
                                {{ __('No shipping methods are available for the provided address.') }}
                            </p>
                        @else
                            <fieldset class="grid gap-4">
                                @foreach ($availableShippingOptions as $option)
                                    <label
                                        class="flex cursor-pointer items-start gap-4 rounded-md border border-gray-200 p-4 shadow-sm hover:border-primary-500"
                                    >
                                        <input
                                            wire:model="selectedShippingOption"
                                            type="radio"
                                            value="{{ $option['id'] }}"
                                            class="mt-1 size-4 border-gray-300 text-primary-600 focus:ring-primary-600"
                                        >
                                        <span class="flex flex-1 flex-col">
                                            <span class="text-sm font-semibold text-gray-800">{{ $option['name'] }}</span>
                                            <span class="text-xs text-gray-500">
                                                {{ $option['formatted_price'] }} · {{ $option['estimated_delivery'] }}
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </fieldset>
                        @endif
                        @error('selectedShippingOption')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <label for="checkout-notes" class="text-sm font-medium text-gray-700">
                            {{ __('Order notes (optional)') }}
                        </label>
                        <textarea
                            wire:model.defer="notes"
                            id="checkout-notes"
                            rows="3"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600"
                        ></textarea>
                        @error('notes')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <x-button type="button" variant="secondary" class="min-w-32 justify-center" wire:click.prevent="previousStep">
                            {{ __('Back to addresses') }}
                        </x-button>
                        <x-button type="submit" class="min-w-32 justify-center">
                            {{ __('Continue to payment') }}
                        </x-button>
                    </div>
                </form>
            @endif

            {{-- Payment step captures the shopper's preferred method. --}}
            @if ($currentStep === 3)
                <form wire:submit.prevent="nextStep" class="space-y-6">
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-gray-700">{{ __('Choose a payment method') }}</h3>
                        <fieldset class="grid gap-4">
                            @foreach ($paymentMethods as $value => $label)
                                <label class="flex cursor-pointer items-center gap-4 rounded-md border border-gray-200 p-4 shadow-sm hover:border-primary-500">
                                    <input
                                        wire:model="selectedPaymentMethod"
                                        type="radio"
                                        value="{{ $value }}"
                                        class="size-4 border-gray-300 text-primary-600 focus:ring-primary-600"
                                    >
                                    <span class="text-sm font-medium text-gray-800">{{ $label }}</span>
                                </label>
                            @endforeach
                        </fieldset>
                        @error('selectedPaymentMethod')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-md border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                        {{-- Display shipping cost and totals so the shopper reviews before paying. --}}
                        <dl class="space-y-2">
                            <div class="flex items-center justify-between">
                                <dt>{{ __('Shipping') }}</dt>
                                <dd>{{ \Illuminate\Support\Number::currency($selectedShippingPrice, current_currency(), app()->getLocale()) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('Total due today') }}</dt>
                                <dd class="text-base font-semibold text-primary-700">
                                    {{ \Illuminate\Support\Number::currency($summary['total'] ?? 0, current_currency(), app()->getLocale()) }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="flex items-center justify-between">
                        <x-button type="button" variant="secondary" class="min-w-32 justify-center" wire:click.prevent="previousStep">
                            {{ __('Back to delivery') }}
                        </x-button>
                        <x-button type="submit" class="min-w-32 justify-center">
                            {{ __('Continue to review') }}
                        </x-button>
                    </div>
                </form>
            @endif

            {{-- Review step confirms all selections before placing the order. --}}
            @if ($currentStep === 4)
                @php
                    $selectedShipping = collect($availableShippingOptions)->firstWhere('id', (int) $selectedShippingOption);
                    $paymentLabel = $paymentMethods[$selectedPaymentMethod] ?? $selectedPaymentMethod;
                    $billingSummary = [
                        __('Name') => trim($billingFirstName . ' ' . $billingLastName),
                        __('Company') => $billingCompany,
                        __('Email address') => $billingEmail,
                        __('Phone number') => $billingPhone,
                        __('Address') => $billingAddress,
                        __('City') => $billingCity,
                        __('Postal code') => $billingPostalCode,
                        __('Country code') => strtoupper($billingCountryCode),
                    ];
                    $shippingSummary = $sameAsShipping
                        ? $billingSummary
                        : [
                            __('Name') => trim($shippingFirstName . ' ' . $shippingLastName),
                            __('Company') => $shippingCompany,
                            __('Address') => $shippingAddress,
                            __('City') => $shippingCity,
                            __('Postal code') => $shippingPostalCode,
                            __('Country code') => strtoupper($shippingCountryCode),
                        ];
                @endphp

                <form wire:submit.prevent="placeOrder" class="space-y-8">
                    <div class="grid gap-6 lg:grid-cols-2">
                        {{-- Billing snapshot so the customer can confirm invoice details. --}}
                        <section class="space-y-3 rounded-md border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-700">{{ __('Billing details') }}</h3>
                            <dl class="space-y-2 text-sm text-gray-700">
                                @foreach ($billingSummary as $label => $value)
                                    @continue(blank($value))
                                    <div class="flex items-start justify-between gap-4">
                                        <dt class="text-gray-500">{{ $label }}</dt>
                                        <dd class="flex-1 text-right font-medium text-gray-800">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </section>

                        {{-- Shipping snapshot mirrors the delivery destination. --}}
                        <section class="space-y-3 rounded-md border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-700">{{ __('Shipping details') }}</h3>
                            <dl class="space-y-2 text-sm text-gray-700">
                                @foreach ($shippingSummary as $label => $value)
                                    @continue(blank($value))
                                    <div class="flex items-start justify-between gap-4">
                                        <dt class="text-gray-500">{{ $label }}</dt>
                                        <dd class="flex-1 text-right font-medium text-gray-800">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </section>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        {{-- Delivery summary reiterates the carrier choice and costs. --}}
                        <section class="space-y-3 rounded-md border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-700">{{ __('Delivery method') }}</h3>
                            <p class="text-sm text-gray-700">
                                {{ data_get($selectedShipping, 'name', __('No shipping method selected')) }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ data_get($selectedShipping, 'formatted_price', \Illuminate\Support\Number::currency($selectedShippingPrice, current_currency(), app()->getLocale())) }}
                                @if (data_get($selectedShipping, 'estimated_delivery'))
                                    · {{ data_get($selectedShipping, 'estimated_delivery') }}
                                @endif
                            </p>
                        </section>

                        {{-- Payment block displays the confirmed tender type. --}}
                        <section class="space-y-3 rounded-md border border-gray-200 p-5">
                            <h3 class="text-sm font-semibold text-gray-700">{{ __('Payment method') }}</h3>
                            <p class="text-sm font-medium text-gray-800">{{ $paymentLabel }}</p>
                        </section>
                    </div>

                    @if (filled($notes))
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-5 text-sm text-gray-700">
                            {{-- Display order notes so the warehouse sees the shopper's request. --}}
                            <h3 class="text-sm font-semibold text-gray-700">{{ __('Order notes') }}</h3>
                            <p class="mt-2 whitespace-pre-line">{{ $notes }}</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <x-button type="button" variant="secondary" class="min-w-32 justify-center" wire:click.prevent="previousStep">
                            {{ __('Back to payment') }}
                        </x-button>
                        <x-button type="submit" class="min-w-32 justify-center" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ __('Place order') }}</span>
                            <span wire:loading>{{ __('Processing...') }}</span>
                        </x-button>
                    </div>
                </form>
            @endif
        </section>

        {{-- Secondary column summarises cart contents and running totals. --}}
        <aside class="space-y-6">
            <div class="rounded-lg border border-gray-200 p-5">
                <h2 class="text-lg font-semibold text-gray-800">{{ __('Order summary') }}</h2>
                <ul class="mt-4 space-y-4 text-sm text-gray-700">
                    @forelse ($cartItems as $item)
                        {{-- Each cart line item is displayed with quantity and extended price. --}}
                        <li class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="font-medium">{{ data_get($item->product, 'name', $item->name) }}</p>
                                <p class="text-xs text-gray-500">{{ __('Quantity') }}: {{ $item->quantity }}</p>
                            </div>
                            <div class="text-right font-semibold">
                                {{ \Illuminate\Support\Number::currency((float) $item->price * (int) $item->quantity, current_currency(), app()->getLocale()) }}
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">
                            {{ __('Your cart is currently empty.') }}
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-lg border border-gray-200 p-5 text-sm text-gray-700">
                {{-- Totals panel keeps financial information visible on every step. --}}
                <dl class="space-y-2">
                    <div class="flex items-center justify-between">
                        <dt>{{ __('Subtotal') }}</dt>
                        <dd>{{ \Illuminate\Support\Number::currency($summary['subtotal'] ?? 0, current_currency(), app()->getLocale()) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt>{{ __('Discounts') }}</dt>
                        <dd>{{ \Illuminate\Support\Number::currency($summary['discount'] ?? 0, current_currency(), app()->getLocale()) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt>{{ __('Shipping') }}</dt>
                        <dd>{{ \Illuminate\Support\Number::currency($summary['shipping'] ?? 0, current_currency(), app()->getLocale()) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt>{{ __('Taxes') }}</dt>
                        <dd>{{ \Illuminate\Support\Number::currency($summary['tax'] ?? 0, current_currency(), app()->getLocale()) }}</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200 pt-3 text-base font-semibold text-primary-700">
                        <dt>{{ __('Total') }}</dt>
                        <dd>{{ \Illuminate\Support\Number::currency($summary['total'] ?? 0, current_currency(), app()->getLocale()) }}</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </x-container>
</div>
