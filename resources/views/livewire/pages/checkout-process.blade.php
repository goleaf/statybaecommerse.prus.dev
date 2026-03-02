{{--
    Checkout process view responsible for presenting the three-step checkout wizard
    (billing → shipping → payment) alongside a live order summary. Each section
    delegates the heavy lifting to Livewire handlers so recalculations propagate
    automatically via component state.
--}}
@php
    $steps = [
        1 => [
            'label'       => __('frontend.billing'),
            'description' => __('frontend.contact_billing_details'),
        ],
        2 => [
            'label'       => __('messages.shipping'),
            'description' => __('frontend.delivery_options'),
        ],
        3 => [
            'label'       => __('messages.payment'),
            'description' => __('frontend.review_pay'),
        ],
    ];
@endphp

<div class="mx-auto max-w-6xl px-4 py-10 lg:px-0">
    <div class="flex flex-col gap-6">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-gray-900">{{ __('frontend.checkout') }}</h1>
            <p class="text-sm text-gray-600">
                {{ __('frontend.checkout_description') }}
            </p>
        </header>

        <nav aria-label="{{ __('frontend.checkout_steps.progress') }}">
            <ol class="flex flex-col gap-6 sm:flex-row sm:items-center sm:gap-8">
                @foreach ($steps as $number => $meta)
                    @php
                        $isComplete = $number < $currentStep;
                        $isCurrent = $number === $currentStep;
                    @endphp
                    <li class="flex items-start gap-3 sm:items-center">
                        <span
                            @class([
                                'flex size-10 shrink-0 items-center justify-center rounded-full border-2 text-sm font-semibold',
                                'border-green-500 bg-green-50 text-green-700' => $isComplete,
                                'border-primary-500 bg-primary-50 text-primary-600' => $isCurrent && ! $isComplete,
                                'border-gray-200 bg-white text-gray-400' => ! $isComplete && ! $isCurrent,
                            ])
                        >
                            @if ($isComplete)
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                {{ $number }}
                            @endif
                        </span>
                        <div>
                            <p
                                @class([
                                    'text-sm font-semibold',
                                    'text-primary-600' => $isCurrent,
                                    'text-gray-900' => $isComplete,
                                    'text-gray-500' => ! $isCurrent && ! $isComplete,
                                ])
                            >
                                {{ $meta['label'] }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $meta['description'] }}</p>
                        </div>
                    </li>
                    @if (! $loop->last)
                        <span class="hidden h-px flex-1 items-center justify-center bg-gray-200 sm:flex" aria-hidden="true"></span>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>

    @if ($cartItems->isEmpty())
        <section
            class="mt-10 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center shadow-sm"
            aria-live="polite"
        >
            <h2 class="text-xl font-semibold text-gray-900">{{ __('messages.your_cart_is_empty') }}</h2>
            <p class="mt-2 text-sm text-gray-600">
                {{ __('frontend.empty_checkout_description') }}
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <button
                    type="button"
                    onclick="window.location.href='{{ route('frontend.cart.index') }}'"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                >
                    {{ __('frontend.view_cart') }}
                </button>
                <button
                    type="button"
                    onclick="window.location.href='{{ route('frontend.products.index') }}'"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
                    {{ __('frontend.continue_shopping') }}
                </button>
            </div>
        </section>
    @else
        <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:items-start">
            <section class="space-y-8">
                @includeWhen($currentStep === 1, 'livewire.pages.checkout.partials.billing', ['countries' => $countries])
                @includeWhen($currentStep === 2, 'livewire.pages.checkout.partials.shipping', ['options' => $availableShippingOptions])
                @includeWhen($currentStep === 3, 'livewire.pages.checkout.partials.payment', ['methods' => $paymentMethods])
            </section>

            <aside class="space-y-6">
                @include('livewire.pages.checkout.partials.order-summary', ['cartItems' => $cartItems, 'summary' => $summary])
            </aside>
        </div>
    @endif
</div>
