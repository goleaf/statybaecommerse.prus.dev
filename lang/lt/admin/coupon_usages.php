<?php

return [
    'title' => 'Kuponų naudojimas',
    'plural' => 'Kuponų naudojimas',
    'single' => 'Kupono naudojimas',
    'form' => [
        'tabs' => [
            'basic_information' => 'Pagrindinė informacija',
            'usage_details' => 'Naudojimo detalės',
            'metadata' => 'Metaduomenys',
        ],
        'sections' => [
            'basic_information' => 'Pagrindinė informacija',
            'usage_details' => 'Naudojimo detalės',
            'metadata' => 'Metaduomenys',
        ],
        'fields' => [
            'coupon' => 'Kuponas',
            'user' => 'Vartotojas',
            'order' => 'Užsakymas',
            'discount_amount' => 'Nuolaidos suma',
            'used_at' => 'Naudota',
            'coupon_name' => 'Kupono pavadinimas',
            'coupon_discount_type' => 'Nuolaidos tipas',
            'user_email' => 'Vartotojo el. paštas',
            'order_total' => 'Užsakymo suma',
            'metadata' => 'Metaduomenys',
            'key' => 'Raktas',
            'value' => 'Reikšmė',
        ],
    ],
    'periods' => [
        'today' => 'Šiandien',
        'this_week' => 'Šią savaitę',
        'this_month' => 'Šį mėnesį',
        'older' => 'Senesni',
    ],
    'filters' => [
        'coupon' => 'Kuponas',
        'user' => 'Vartotojas',
        'order' => 'Užsakymas',
        'used_at' => 'Naudota',
        'used_today' => 'Naudota šiandien',
        'used_this_week' => 'Naudota šią savaitę',
        'used_this_month' => 'Naudota šį mėnesį',
    ],
    'actions' => [
        'export_usage_report' => 'Eksportuoti naudojimo ataskaitą',
        'export_bulk_report' => 'Eksportuoti masinę ataskaitą',
    ],
    'notifications' => [
        'usage_report_exported_successfully' => 'Naudojimo ataskaita sėkmingai eksportuota',
        'bulk_report_exported_successfully' => 'Masinė ataskaita sėkmingai eksportuota',
    ],
];

