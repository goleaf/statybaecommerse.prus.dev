{{--
    Checkout process multi-step container.
    This placeholder ensures Livewire components render during feature tests
    while the full UI is provided by the Volt/Blade templates in the storefront package.
--}}
<div class="space-y-6">
    <h1 class="text-2xl font-semibold">{{ __('Checkout') }}</h1>

    {{-- Billing summary --}}
    <section class="rounded-lg border border-gray-200 p-4">
        <h2 class="text-lg font-medium">{{ __('Billing details') }}</h2>
        <p class="text-sm text-gray-600">{{ $billingFirstName }} {{ $billingLastName }}</p>
        <p class="text-sm text-gray-600">{{ $billingAddress }}, {{ $billingCity }} {{ $billingPostalCode }}</p>
    </section>

    {{-- Shipping summary --}}
    <section class="rounded-lg border border-gray-200 p-4">
        <h2 class="text-lg font-medium">{{ __('Shipping details') }}</h2>
        <p class="text-sm text-gray-600">{{ $shippingFirstName }} {{ $shippingLastName }}</p>
        <p class="text-sm text-gray-600">{{ $shippingAddress }}, {{ $shippingCity }} {{ $shippingPostalCode }}</p>
    </section>
</div>
