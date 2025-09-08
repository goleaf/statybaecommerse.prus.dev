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

<span>
    {{ \Illuminate\Support\Number::currency($price ?? 0, function_exists('current_currency') ? current_currency() : 'USD', app()->getLocale()) }}
</span>
