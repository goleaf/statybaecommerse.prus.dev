<?php

return [
    'plural' => 'Nuolaidos panaudojimai',
    'single' => 'Nuolaidos panaudojimas',

    'form' => [
        'sections' => [
            'basic_information' => 'Pagrindinė informacija',
        ],
        'fields' => [
            'discount_code' => 'Nuolaidos kodas',
            'user' => 'Vartotojas',
            'order' => 'Užsakymas',
            'discount_amount' => 'Nuolaidos suma',
            'redeemed_at' => 'Panaudota',
        ],
    ],

    'table' => [
        'discount_code' => 'Nuolaidos kodas',
        'user' => 'Vartotojas',
        'order' => 'Užsakymas',
        'discount_amount' => 'Nuolaidos suma',
        'redeemed_at' => 'Panaudota',
        'created_at' => 'Sukurta',
    ],

    'filters' => [
        'discount_code' => 'Nuolaidos kodas',
        'user' => 'Vartotojas',
        'redeemed_at' => 'Panaudota',
        'recent' => 'Naujausi',
    ],

    'actions' => [
        'refund' => 'Grąžinti',
        'bulk_refund' => 'Grąžinti pasirinktus',
    ],

    'refund_successful' => 'Sėkmingai grąžinta',
    'bulk_refund_successful' => 'Sėkmingai grąžinti pasirinkti įrašai',
];
