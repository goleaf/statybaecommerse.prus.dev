{{--
    Checkout process view responsible for presenting the four-step checkout wizard
    (addresses → delivery → payment → review) alongside a live order summary.
    Each section delegates heavy logic to specialised Livewire components so
    recalculations propagate automatically via events.
--}}
<div class="mx-auto max-w-6xl px-4 py-10 lg:px-0">
    {{-- Top-of-page step indicator keeps shoppers oriented within the wizard. --}}
    <x-steps
        :current="$currentStep"
        :labels="[
            __('Addresses'),
            __('Delivery'),
            __('Payment'),
            __('Review'),
        ]"
    />

    <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:items-start">
        {{-- Main column renders the active checkout step. --}}
        <section class="space-y-8">
            @if ($currentStep === 1)
                {{-- Addresses step hosts the billing/shipping form component. --}}
                <livewire:components.checkout.addresses />

                <div class="flex justify-end">
                    <x-buttons.primary wire:click="toStep(2)">
                        {{ __('Continue to delivery') }}
                    </x-buttons.primary>
                </div>
            @elseif ($currentStep === 2)
                {{-- Delivery step handles shipping method selection and pricing events. --}}
                <livewire:components.checkout.delivery />

                <div class="flex flex-wrap justify-between gap-3">
                    <x-buttons.secondary wire:click="toStep(1)">
                        {{ __('Back to addresses') }}
                    </x-buttons.secondary>
                    <x-buttons.primary wire:click="toStep(3)">
                        {{ __('Continue to payment') }}
                    </x-buttons.primary>
                </div>
            @elseif ($currentStep === 3)
                {{-- Payment step surfaces the available payment providers. --}}
                <livewire:components.checkout.payment />

                <div class="flex flex-wrap justify-between gap-3">
                    <x-buttons.secondary wire:click="toStep(2)">
                        {{ __('Back to delivery') }}
                    </x-buttons.secondary>
                    <x-buttons.primary wire:click="toStep(4)">
                        {{ __('Review order') }}
                    </x-buttons.primary>
                </div>
            @else
                {{-- Final review step summarises the order before submission. --}}
                <livewire:components.checkout.review />

                <div class="flex flex-wrap justify-between gap-3">
                    <x-buttons.secondary wire:click="toStep(3)">
                        {{ __('Back to payment') }}
                    </x-buttons.secondary>
                    <x-buttons.primary wire:click="$dispatch('confirm-checkout')">
                        {{ __('Place order') }}
                    </x-buttons.primary>
                </div>
            @endif
        </section>

        {{-- Side column keeps the order summary sticky on desktop for clarity. --}}
        <aside class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Order summary') }}</h2>

            {{-- Dedicated component listens for refresh events emitted by the page class. --}}
            <livewire:components.checkout.order-summary />
        </aside>
    </div>
</div>
