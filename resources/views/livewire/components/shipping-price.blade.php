<?php
if (function_exists('\\Livewire\\Volt\\state') && function_exists('\\Livewire\\Volt\\on')) {
    \Livewire\Volt\state(['price' => 0]);

    \Livewire\Volt\on([
        'cart-price-update' => function () {
            $this->price = data_get(session()->get('checkout'), 'shipping_option') ? data_get(session()->get('checkout'), 'shipping_option')[0]['price'] : 0;
        },
    ]);
}

?>

<span class="inline-flex items-center gap-2">
    {{-- Indicate rate recalculations while Livewire fetches totals --}}
    <span
        wire:loading.flex
        class="items-center gap-2 text-xs text-gray-500"
    >
        <x-loading-dots class="text-primary-600" aria-hidden="true" />
        <span>{{ __('Updating shipping...') }}</span>
    </span>

    <span wire:loading.remove>
        {{ \Illuminate\Support\Number::currency($price ?? 0, function_exists('current_currency') ? current_currency() : 'USD', app()->getLocale()) }}
    </span>
</span>
