<?php

declare(strict_types=1);

return [
    'title'  => 'Nuolaidų panaudojimai',
    'plural' => 'Nuolaidų panaudojimai',
    'single' => 'Nuolaidos panaudojimas',
    'list_item_label' => 'Nuolaida :discount',
    'list_item_tooltip' => 'Sutaupyta :amount su kodu :code',

    'sections' => [
        'associations'           => 'Susijusios reikšmės',
        'redemption_details'     => 'Panaudojimo informacija',
        'additional_information' => 'Papildoma informacija',
    ],

    'fields' => [
        'discount'       => 'Nuolaida',
        'code'           => 'Nuolaidos kodas',
        'user'           => 'Klientas',
        'order'          => 'Užsakymas',
        'amount_saved'   => 'Sutaupyta suma',
        'currency_code'  => 'Valiuta',
        'status'         => 'Būsena',
        'redeemed_at'    => 'Panaudota',
        'ip_address'     => 'IP adresas',
        'user_agent'     => 'Naršyklė',
        'notes'          => 'Pastabos',
        'metadata'       => 'Papildomi duomenys',
        'metadata_key'   => 'Raktas',
        'metadata_value' => 'Reikšmė',
    ],

    'statuses' => [
        'pending'   => 'Laukiama',
        'redeemed'  => 'Panaudota',
        'expired'   => 'Negalioja',
        'cancelled' => 'Atšaukta',
    ],

    'filters' => [
        'redeemed_from'  => 'Panaudota nuo',
        'redeemed_until' => 'Panaudota iki',
        'has_order'      => 'Turi užsakymą',
    ],
];
