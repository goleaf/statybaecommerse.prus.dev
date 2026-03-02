<?php

declare(strict_types=1);

$base = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');

return array_replace_recursive($base, [
    'accepted'   => ':attribute turi būti patvirtintas.',
    'active_url' => ':attribute nėra tinkamas URL adresas.',
    'after'      => ':attribute turi būti data vėlesnė nei :date.',
    'email'      => ':attribute turi būti galiojantis el. pašto adresas.',
    'exists'     => 'Pasirinktas :attribute yra neteisingas.',
    'lowercase'  => ':attribute turi būti tik mažosiomis raidėmis.',
    'required'   => 'Privaloma užpildyti lauką „:attribute“.',
    'confirmed'  => ':attribute patvirtinimas nesutampa.',
    'regex'      => ':attribute formatas neteisingas.',
    'unique'     => 'Toks :attribute jau naudojamas.',
    'min'        => [
        'string' => ':attribute turi būti bent :min simbolių.',
    ],
    'max'        => [
        'string' => ':attribute negali būti ilgesnis nei :max simbolių.',
    ],
    'attributes' => [
        'first_name'            => 'vardas',
        'last_name'             => 'pavardė',
        'email'                 => 'el. paštas',
        'password'              => 'slaptažodis',
        'password_confirmation' => 'slaptažodžio patvirtinimas',
        'loginForm' => [
            'email'           => 'el. paštas',
            'password'        => 'slaptažodis',
            'captchaToken'    => 'saugos žetonas',
            'captchaResponse' => 'saugos patikros atsakymas',
        ],
        'registrationForm' => [
            'first_name'            => 'vardas',
            'last_name'             => 'pavardė',
            'email'                 => 'el. paštas',
            'password'              => 'slaptažodis',
            'password_confirmation' => 'slaptažodžio patvirtinimas',
        ],
        'selectedShippingOption' => 'pristatymo būdas',
        'selectedPaymentMethod'  => 'mokėjimo būdas',
        'billing'                => [
            'first_name'  => 'sąskaitos vardas',
            'last_name'   => 'sąskaitos pavardė',
            'email'       => 'sąskaitos el. paštas',
            'phone'       => 'sąskaitos telefono numeris',
            'address'     => 'sąskaitos adresas',
            'city'        => 'sąskaitos miestas',
            'postal_code' => 'sąskaitos pašto kodas',
            'country'     => 'sąskaitos šalis',
        ],
        'shipping' => [
            'first_name'  => 'pristatymo vardas',
            'last_name'   => 'pristatymo pavardė',
            'address'     => 'pristatymo adresas',
            'city'        => 'pristatymo miestas',
            'postal_code' => 'pristatymo pašto kodas',
            'country'     => 'pristatymo šalis',
        ],
    ],
]);
