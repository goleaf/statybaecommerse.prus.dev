@php
    $pickShippingValue = static function (string $key) use ($shipping, $billing, $sameAsShipping): string {
        $shippingValue = $shipping[$key] ?? null;
        if (is_string($shippingValue) && trim($shippingValue) !== '') {
            return $shippingValue;
        }

        $billingValue = $billing[$key] ?? null;
        if ($sameAsShipping || (is_string($billingValue) && trim($billingValue) !== '')) {
            return (string) ($billingValue ?? '');
        }

        return '';
    };

    $resolveCountryLabel = static function (string $countryCode) use ($countries): string {
        $code = strtoupper(trim($countryCode));
        if ($code === '') {
            return '';
        }

        if (isset($countries) && is_iterable($countries)) {
            foreach ($countries as $country) {
                if (
                    is_array($country)
                    && strtoupper((string) ($country['code'] ?? '')) === $code
                    && is_string($country['name'] ?? null)
                    && trim($country['name']) !== ''
                ) {
                    return $country['name'];
                }
            }
        }

        return $code;
    };

    $shippingName = trim(sprintf(
        '%s %s',
        $pickShippingValue('first_name'),
        $pickShippingValue('last_name'),
    ));

    $shippingLines = array_filter([
        $shippingName !== '' ? $shippingName : null,
        $pickShippingValue('company') !== '' ? $pickShippingValue('company') : null,
        $pickShippingValue('address') !== '' ? $pickShippingValue('address') : null,
        trim(sprintf(
            '%s %s',
            $pickShippingValue('city'),
            $pickShippingValue('postal_code'),
        )),
        $resolveCountryLabel($pickShippingValue('country')),
    ]);
@endphp

<section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <header class="space-y-1">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.shipping') }}</h2>
        <p class="text-sm text-gray-500">
            {{ __('ui.confirm_where_we_should_send_your_order_and_choose_a_delivery_option_that_works_best_for_you') }}
        </p>
    </header>

    <div class="mt-6 space-y-4">
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ __('ui.shipping_address') }}</p>
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        @forelse ($shippingLines as $line)
                            @if ($line !== '')
                                <li>{{ $line }}</li>
                            @endif
                        @empty
                            <li>{{ __('ui.no_shipping_address_provided_yet') }}</li>
                        @endforelse
                    </ul>
                </div>
                <button type="button" wire:click="toStep(1)" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                    {{ __('ui.edit_address') }}
                </button>
            </div>
        </div>

        <form wire:submit.prevent="toStep(3)" class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('ui.delivery_options') }}</h3>
                <button
                    type="button"
                    wire:click="resolveShippingOptions(true)"
                    wire:loading.attr="disabled"
                    wire:target="resolveShippingOptions"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ __('ui.refresh_options') }}
                </button>
            </div>

            <div
                wire:loading.flex
                wire:target="resolveShippingOptions"
                class="items-center gap-2 rounded-lg border border-dashed border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-700"
                role="status"
            >
                <x-loading-dots class="text-primary-600" aria-hidden="true" />
                <span>{{ __('ui.updating_delivery_options') }}</span>
            </div>

            <fieldset class="space-y-3" aria-label="{{ __('ui.delivery_options') }}" wire:loading.remove wire:target="resolveShippingOptions">
                @forelse ($options as $option)
                    @php($isSelectedOption = (string) ($selectedShippingOption ?? '') === (string) ($option['id'] ?? ''))
                    <label
                        wire:key="checkout-delivery-option-{{ $option['id'] }}"
                        @class([
                            'relative flex flex-wrap items-start justify-between gap-4 rounded-xl border-2 p-4 transition-colors duration-200',
                            'border-green-300 bg-green-50/40' => $isSelectedOption,
                            'border-gray-200 bg-white hover:border-green-300 hover:bg-green-50/40' => ! $isSelectedOption,
                        ])
                    >
                        <span class="flex flex-1 items-start gap-3">
                            <input
                                type="radio"
                                value="{{ $option['id'] }}"
                                wire:model="selectedShippingOption"
                                class="mt-0.5 size-4 border-gray-300 text-green-600 accent-green-600 focus:ring-0 focus:ring-offset-0"
                            >
                            <span class="flex flex-col">
                                <span class="block text-sm font-semibold text-gray-900">{{ $option['name'] }}</span>
                                @if(! empty($option['estimated_delivery']))
                                    <span class="block text-xs text-gray-500">{{ $option['estimated_delivery'] }}</span>
                                @endif
                                @if(! empty($option['badges']) && is_array($option['badges']))
                                    <span class="mt-2 flex flex-wrap gap-2">
                                        @foreach($option['badges'] as $badge)
                                            @if(is_array($badge) && ! empty($badge['label']))
                                                <span
                                                    @class([
                                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide',
                                                        'bg-green-100 text-green-800' => ($badge['type'] ?? null) === 'free',
                                                        'bg-primary-100 text-primary-800' => ($badge['type'] ?? null) !== 'free',
                                                    ])
                                                >
                                                    {{ $badge['label'] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </span>
                                @endif
                            </span>
                        </span>
                        <span class="flex flex-col items-end text-right">
                            <span class="text-sm font-semibold text-primary-700">
                                {{ $option['formatted_price'] ?? app_money_format($option['price'], $option['currency_code'] ?? current_currency()) }}
                            </span>
                            @if(isset($option['original_price']) && $option['original_price'] > $option['price'])
                                <span class="text-xs text-gray-400 line-through">
                                    {{ app_money_format($option['original_price'], $option['currency_code'] ?? current_currency()) }}
                                </span>
                            @endif
                        </span>
                    </label>
                @empty
                    <p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ __('ui.no_shipping_methods_are_available_for_the_specified_address_please_update_the_address_or_contact_support') }}
                    </p>
                @endforelse
            </fieldset>

            @error('selectedShippingOption')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex flex-wrap justify-between gap-3 pt-2">
                <button type="button" wire:click="toStep(1)" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                    {{ __('ui.back_to_billing') }}
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                    wire:target="toStep,resolveShippingOptions"
                >
                    {{ __('ui.continue_to_payment') }}
                </button>
            </div>
        </form>
    </div>
</section>
