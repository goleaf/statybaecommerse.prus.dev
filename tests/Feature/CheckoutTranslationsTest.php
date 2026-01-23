<?php

declare(strict_types=1);

test('checkout translations exist in both languages', function () {
    $keys = [
        'checkout_back_to_store',
        'checkout_order_information',
        'cart_summary',
        'checkout_applied_coupon',
        'Sub total',
        'Taxes',
    ];

    foreach ($keys as $key) {
        // Test Lithuanian translations
        expect(__($key, [], 'lt'))->not->toBe($key);

        // Test English translations
        expect(__($key, [], 'en'))->not->toBe($key);
    }
});

test('checkout template uses translation functions', function () {
    $template = file_get_contents(resource_path('views/livewire/pages/checkout.blade.php'));

    // Check that hardcoded strings are replaced with translation functions
    expect($template)->toContain("{{ __('checkout_back_to_store') }}");
    expect($template)->toContain("{{ __('checkout_order_information') }}");
    expect($template)->toContain("{{ __('cart_summary') }}");
    expect($template)->toContain("{{ __('checkout_applied_coupon') }}");
});
