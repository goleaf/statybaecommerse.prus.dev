<?php

declare(strict_types=1);

$base = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');

return array_replace_recursive($base, [
    'accepted'  => 'Поле :attribute должно быть принято.',
    'email'     => 'Поле :attribute должно быть действительным адресом электронной почты.',
    'exists'    => 'Выбранное значение :attribute недопустимо.',
    'lowercase' => 'Поле :attribute должно содержать только строчные буквы.',
    'required'  => 'Поле :attribute обязательно.',
    'confirmed' => 'Подтверждение поля :attribute не совпадает.',
    'regex'     => 'Поле :attribute имеет неверный формат.',
    'max'       => [
        'string' => 'Поле :attribute не должно превышать :max символов.',
    ],
    'min' => [
        'string' => 'Поле :attribute должно содержать не менее :min символов.',
    ],
    'unique'     => 'Такое значение :attribute уже используется.',
    'attributes' => [
        'first_name'            => 'имя',
        'last_name'             => 'фамилия',
        'email'                 => 'эл. почта',
        'password'              => 'пароль',
        'password_confirmation' => 'подтверждение пароля',
        'loginForm'             => [
            'email'           => 'эл. почта',
            'password'        => 'пароль',
            'captchaToken'    => 'токен безопасности',
            'captchaResponse' => 'ответ проверки безопасности',
        ],
        'registrationForm' => [
            'first_name'            => 'имя',
            'last_name'             => 'фамилия',
            'email'                 => 'эл. почта',
            'password'              => 'пароль',
            'password_confirmation' => 'подтверждение пароля',
        ],
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
