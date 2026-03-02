<?php

declare(strict_types=1);

$base = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');

return array_replace_recursive($base, [
    'accepted'   => 'The :attribute field must be accepted.',
    'attributes' => [
        'selectedShippingOption' => 'shipping method',
        'selectedPaymentMethod'  => 'payment method',
        'billing'                => [
            'first_name'  => 'billing first name',
            'last_name'   => 'billing last name',
            'email'       => 'billing email',
            'phone'       => 'billing phone',
            'address'     => 'billing address',
            'city'        => 'billing city',
            'postal_code' => 'billing postal code',
            'country'     => 'billing country',
        ],
        'shipping' => [
            'first_name'  => 'shipping first name',
            'last_name'   => 'shipping last name',
            'address'     => 'shipping address',
            'city'        => 'shipping city',
            'postal_code' => 'shipping postal code',
            'country'     => 'shipping country',
        ],
    ],
]);
