<?php

declare(strict_types=1);

return [
    // Navigation
    'navigation' => [
        'partners' => 'Partneriai',
    ],

    // Models
    'models' => [
        'partner'  => 'Partneris',
        'partners' => 'Partneriai',
    ],

    // Fields
    'fields' => [
        'name'            => 'Pavadinimas',
        'code'            => 'Kodas',
        'tier'            => 'Lygių',
        'is_enabled'      => 'Įjungtas',
        'contact_email'   => 'Kontaktinis el. paštas',
        'contact_phone'   => 'Kontaktinis telefonas',
        'discount_rate'   => 'Nuolaidos procentas',
        'commission_rate' => 'Komisijos procentas',
        'logo'            => 'Logotipas',
        'banner'          => 'Baneris',
        'created_at'      => 'Sukurta',
        'updated_at'      => 'Atnaujinta',
    ],

    // Sections
    'sections' => [
        'basic_information'   => 'Pagrindinė informacija',
        'contact_information' => 'Kontaktinė informacija',
        'financial_settings'  => 'Finansiniai nustatymai',
        'media'               => 'Medija',
    ],

    // Actions
    'actions' => [
        'create' => 'Sukurti',
        'view'   => 'Peržiūrėti',
        'edit'   => 'Redaguoti',
        'delete' => 'Ištrinti',
    ],

    // Help text
    'name_help'            => 'Partnerio pavadinimas',
    'code_help'            => 'Unikalus partnerio kodas',
    'tier_help'            => 'Partnerio lygių',
    'contact_email_help'   => 'Kontaktinis el. paštas',
    'contact_phone_help'   => 'Kontaktinis telefonas',
    'discount_rate_help'   => 'Nuolaidos procentas (0-100)',
    'commission_rate_help' => 'Komisijos procentas (0-100)',
    'logo_help'            => 'Partnerio logotipas',
    'banner_help'          => 'Partnerio baneris',

    'dashboard' => [
        'title'        => 'Partnerių užsakymai',
        'subtitle'     => 'Stebėkite partnerių užsakymų būsenas ir apyvartą vienoje vietoje.',
        'result_count' => 'Iš viso :count užsakymų',
        'tabs'         => [
            'open'      => 'Atviri',
            'shipped'   => 'Išsiųsti',
            'cancelled' => 'Atšaukti',
        ],
        'table' => [
            'order'          => 'Užsakymas',
            'status'         => 'Būsena',
            'payment_status' => 'Mokėjimas',
            'items'          => 'Pozicijos',
            'items_count'    => '{0}Pozicijų nėra|{1}:count pozicija|[2,*]:count pozicijos',
            'total'          => 'Suma',
            'placed_at'      => 'Sukurta',
        ],
        'empty' => [
            'title'       => 'Pagal pasirinktą filtrą užsakymų nėra',
            'description' => 'Pakoreguokite būsenos filtrą arba patikrinkite vėliau, kai atsiras naujų partnerių užsakymų.',
        ],
        'errors' => [
            'forbidden' => [
                'title'       => 'Reikalinga partnerio prieiga',
                'description' => 'Jūsų paskyra nesusieta su aktyviu partneriu. Susisiekite su palaikymo komanda, kad gautumėte prieigą.',
            ],
            'unauthorized' => [
                'title'       => 'Prisijunkite',
                'description' => 'Prisijunkite partnerio paskyra, kad matytumėte partnerių užsakymus.',
            ],
        ],
    ],
];
