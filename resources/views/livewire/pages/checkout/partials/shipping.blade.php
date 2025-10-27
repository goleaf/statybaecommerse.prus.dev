@php
    $shippingName = trim(sprintf(
        '%s %s',
        $shipping['first_name'] ?? ($sameAsShipping ? $billing['first_name'] ?? '' : ''),
        $shipping['last_name'] ?? ($sameAsShipping ? $billing['last_name'] ?? '' : ''),
    ));

    $shippingLines = array_filter([
        $shippingName !== '' ? $shippingName : null,
        $shipping['company'] ?? ($sameAsShipping ? $billing['company'] ?? null : null),
        $shipping['address'] ?? ($sameAsShipping ? $billing['address'] ?? null : null),
        trim(sprintf(
            '%s %s',
            $shipping['city'] ?? ($sameAsShipping ? $billing['city'] ?? '' : ''),
            $shipping['postal_code'] ?? ($sameAsShipping ? $billing['postal_code'] ?? '' : ''),
        )),
        strtoupper($shipping['country'] ?? ($sameAsShipping ? $billing['country'] ?? '' : '')),
    ]);
@endphp

<section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <header class="space-y-1">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('Shipping') }}</h2>
        <p class="text-sm text-gray-500">
            {{ __('Confirm where we should send your order and choose a delivery option that works best for you.') }}
        </p>
    </header>

    <div class="mt-6 space-y-4">
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ __('Shipping address') }}</p>
                    <ul class="mt-2 space-y-1 text-sm text-gray-600">
                        @forelse ($shippingLines as $line)
                            @if ($line !== '')
                                <li>{{ $line }}</li>
                            @endif
                        @empty
                            <li>{{ __('No shipping address provided yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
                <x-buttons.secondary type="button" wire:click="toStep(1)" class="px-3 py-1.5 text-xs">
                    {{ __('Edit address') }}
                </x-buttons.secondary>
            </div>
        </div>

        <form wire:submit.prevent="toStep(3)" class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('Delivery options') }}</h3>
                <button
                    type="button"
                    wire:click="resolveShippingOptions(true)"
                    wire:loading.attr="disabled"
                    wire:target="resolveShippingOptions"
                    class="text-xs font-medium text-primary-600 hover:text-primary-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500"
                >
                    {{ __('Refresh options') }}
                </button>
            </div>

            <div
                wire:loading.flex
                wire:target="resolveShippingOptions"
                class="items-center gap-2 rounded-lg border border-dashed border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-700"
                role="status"
            >
                <x-loading-dots class="text-primary-600" aria-hidden="true" />
                <span>{{ __('Updating delivery options…') }}</span>
            </div>

            <fieldset class="space-y-3" aria-label="{{ __('Delivery options') }}" wire:loading.remove wire:target="resolveShippingOptions">
                @forelse ($options as $option)
                    <label
                        wire:key="checkout-delivery-option-{{ $option['id'] }}"
                        @class([
                            'flex flex-wrap items-start justify-between gap-4 rounded-lg border p-4 transition',
                            'border-primary-200 bg-primary-50 shadow-sm' => (int) $selectedShippingOption === (int) $option['id'],
                            'border-gray-200 hover:border-primary-200 hover:bg-primary-50/50' => (int) $selectedShippingOption !== (int) $option['id'],
                        ])
                    >
                        <span class="flex flex-1 items-start gap-3">
                            <input
                                type="radio"
                                value="{{ $option['id'] }}"
                                wire:model="selectedShippingOption"
                                class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600"
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
                        {{ __('No shipping methods are available for the specified address. Please update the address or contact support.') }}
                    </p>
                @endforelse
            </fieldset>

            @error('selectedShippingOption')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex flex-wrap justify-between gap-3 pt-2">
                <x-buttons.secondary type="button" wire:click="toStep(1)">
                    {{ __('Back to billing') }}
                </x-buttons.secondary>
                <x-buttons.primary
                    type="submit"
                    class="px-6 py-2 text-sm"
                    wire:loading.attr="disabled"
                    wire:target="toStep,resolveShippingOptions"
                >
                    {{ __('Continue to payment') }}
                </x-buttons.primary>
            </div>
        </form>
    </div>
</section>
