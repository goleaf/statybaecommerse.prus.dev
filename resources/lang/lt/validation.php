<?php

return [
    'required' => 'Laukas :attribute yra privalomas.',
    'email' => ':attribute turi būti galiojantis el. pašto adresas.',
    'min' => [
        'string' => ':attribute turi būti ne trumpesnis nei :min simbolių.',
        'numeric' => ':attribute turi būti ne mažesnis nei :min.',
    ],
    'max' => [
        'string' => ':attribute negali būti ilgesnis nei :max simbolių.',
        'numeric' => ':attribute negali būti didesnis nei :max.',
    ],
    'confirmed' => ':attribute patvirtinimas nesutampa.',
    'attributes' => [
        'email' => 'el. paštas',
        'password' => 'slaptažodis',
        'first_name' => 'vardas',
        'last_name' => 'pavardė',
    ],
];
