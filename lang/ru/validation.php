<?php

declare(strict_types=1);

$base = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');

return array_replace_recursive($base, [
    'accepted' => 'Поле :attribute должно быть принято.',
    'exists'   => 'Выбранное значение :attribute недопустимо.',
    'required' => 'Поле :attribute обязательно.',
    'max'      => [
        'string' => 'Поле :attribute не должно превышать :max символов.',
    ],
    'attributes' => [
        'selectedShippingOption' => 'способ доставки',
        'selectedPaymentMethod'  => 'способ оплаты',
        'billing'                => [
            'first_name'  => 'имя плательщика',
            'last_name'   => 'фамилия плательщика',
            'email'       => 'электронная почта плательщика',
            'phone'       => 'телефон плательщика',
            'address'     => 'платежный адрес',
            'city'        => 'город плательщика',
            'postal_code' => 'почтовый индекс плательщика',
            'country'     => 'страна плательщика',
        ],
        'shipping' => [
            'first_name'  => 'имя получателя',
            'last_name'   => 'фамилия получателя',
            'address'     => 'адрес доставки',
            'city'        => 'город доставки',
            'postal_code' => 'почтовый индекс доставки',
            'country'     => 'страна доставки',
        ],
    ],
]);
