<section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    @php
        $hasMontonioOptions = $selectedPaymentMethod === \App\Enums\PaymentMethod::MONTONIO->value
            && $montonioPaymentMethodOptions !== [];
        $showMontonioBanks = $hasMontonioOptions
            && $selectedMontonioPaymentMethodType === 'paymentInitiation'
            && $montonioBankOptions !== [];
    @endphp

    <header class="space-y-1">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.payment') }}</h2>
        <p class="text-sm text-gray-500">
            {{ __('ui.choose_how_you_would_like_to_pay_and_add_any_notes_for_our_team_before_placing_your_order') }}
        </p>
    </header>

    <form wire:submit.prevent="placeOrder" class="mt-6 space-y-8">
        @if ((bool) config('montonio.sandbox') && (bool) config('montonio.demo_mode'))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                {{ __('messages.sandbox_demo_payment_mode_enabled') }}
            </div>
        @endif

        @error('selectedPaymentMethod')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="space-y-6">
            @if ($isResolvingMontonioPaymentOptions)
                <div class="flex items-center gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                    <x-loading-dots class="text-primary-600" aria-hidden="true" />
                    <span>{{ __('translations.preparing_payment_methods') }}</span>
                </div>
            @endif

            @if ($montonioPaymentOptionsError !== null)
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ $montonioPaymentOptionsError }}
                </div>
            @endif

            @if ($hasMontonioOptions)
                <fieldset class="space-y-3" aria-label="{{ __('ui.choose_payment_type') }}">
                    <legend class="text-sm font-semibold text-gray-900">{{ __('ui.choose_payment_type') }}</legend>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($montonioPaymentMethodOptions as $methodType => $option)
                            @php
                                $isSelected = $selectedMontonioPaymentMethodType === $methodType;
                                $helperText = $methodType === 'paymentInitiation'
                                    ? __('ui.pay_with_your_bank')
                                    : __('ui.pay_with_card');
                            @endphp

                            <label
                                wire:key="montonio-payment-method-{{ $methodType }}"
                                @class([
                                    'relative block cursor-pointer rounded-xl border p-4 transition duration-200 ease-in-out',
                                    'border-gray-900 bg-white shadow-md ring-1 ring-gray-900' => $isSelected,
                                    'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/50 hover:shadow-sm' => ! $isSelected,
                                ])
                            >
                                <input
                                    type="radio"
                                    value="{{ $methodType }}"
                                    wire:model.live="selectedMontonioPaymentMethodType"
                                    class="sr-only"
                                >

                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $option['label'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $helperText }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    @if ($option['logo_url'])
                                        <span class="flex h-10 items-center rounded-lg border border-gray-200 bg-white px-3 py-2">
                                            <img
                                                src="{{ $option['logo_url'] }}"
                                                alt="{{ $option['label'] }}"
                                                class="max-h-6 w-auto object-contain"
                                                loading="lazy"
                                            >
                                        </span>
                                    @endif

                                    @foreach ($option['preview_logos'] as $logoUrl)
                                        <span class="flex h-10 items-center rounded-lg border border-gray-200 bg-white px-3 py-2">
                                            <img
                                                src="{{ $logoUrl }}"
                                                alt=""
                                                class="max-h-6 w-auto object-contain"
                                                loading="lazy"
                                            >
                                        </span>
                                    @endforeach
                                </div>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                @error('selectedMontonioPaymentMethodType')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                @if ($showMontonioBanks)
                    <fieldset class="space-y-3" aria-label="{{ __('ui.choose_bank') }}">
                        <legend class="text-sm font-semibold text-gray-900">{{ __('ui.choose_bank') }}</legend>


                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                            @foreach ($montonioBankOptions as $bank)
                                @php
                                    $isSelectedBank = $selectedMontonioBankCode === $bank['code'];
                                @endphp

                                <label
                                    wire:key="montonio-bank-{{ $bank['code'] }}"
                                    @class([
                                        'group relative flex cursor-pointer flex-col items-center justify-between gap-3 rounded-xl border p-3 text-center transition duration-200 ease-in-out',
                                        'border-gray-900 bg-white shadow-md ring-1 ring-gray-900 z-10' => $isSelectedBank,
                                        'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/50 hover:shadow-sm' => ! $isSelectedBank,
                                    ])
                                >
                                    <input
                                        type="radio"
                                        value="{{ $bank['code'] }}"
                                        wire:model.live="selectedMontonioBankCode"
                                        class="sr-only"
                                    >

                                    <div class="flex h-12 w-full items-center justify-center">
                                        @if ($bank['logo_url'])
                                            <img
                                                src="{{ $bank['logo_url'] }}"
                                                alt="{{ $bank['name'] }}"
                                                class="max-h-10 w-auto max-w-full object-contain"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="space-y-1">
                                        <span @class([
                                            'block text-xs font-medium transition-colors duration-200',
                                            'text-gray-900' => $isSelectedBank,
                                            'text-gray-500 group-hover:text-gray-900' => ! $isSelectedBank,
                                        ])>
                                            {{ $bank['name'] }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    @error('selectedMontonioBankCode')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @endif
            @elseif (! $isResolvingMontonioPaymentOptions && $montonioPaymentOptionsError === null)
                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                    {{ __('ui.payment_options_will_appear_here_when_montonio_api_methods_are_available') }}
                </div>
            @endif
        </div>

        <div>
            <label for="order_notes" class="block text-sm font-medium text-gray-700">
                {{ __('ui.order_notes_optional') }}
            </label>
            <textarea
                id="order_notes"
                rows="4"
                wire:model.defer="notes"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
            ></textarea>
            @error('notes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-xs leading-5 text-gray-500">
            {{ __('messages.checkout_terms_acknowledgement') }}
        </p>

        <div class="flex flex-wrap justify-between gap-3 pt-2">
            <button type="button" wire:click="toStep(2)" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/20">
                {{ __('ui.back_to_shipping') }}
            </button>
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                wire:target="resolveShippingOptions,placeOrder"
            >
                <span class="flex items-center gap-2">
                    <span wire:loading.flex wire:target="placeOrder">
                        <x-loading-dots class="text-white" aria-hidden="true" />
                    </span>
                    {{ __('ui.place_order') }}
                </span>
            </button>
        </div>
    </form>
</section>
