<?php

declare(strict_types=1);

return [
    'actions' => [
        'test_all'       => 'Išbandyti viską',
        'test_condition' => 'Išbandyti sąlygą',
        'view_discount'  => 'Peržiūrėti nuolaidą',
    ],
    'empty' => [
        'description' => 'Nėra nuolaidų sąlygų',
        'title'       => 'Sąlygų nerasta',
    ],
    'fields' => [
        'condition'  => 'Sąlyga',
        'created_at' => 'Sukurta',
        'discount'   => 'Nuolaida',
        'is_active'  => 'Aktyvus',
        'metadata'   => 'Metaduomenys',
        'operator'   => 'Operatorius',
        'position'   => 'Pozicija',
        'priority'   => 'Prioritetas',
        'test_value' => 'Bandymo reikšmė',
        'type'       => 'Tipas',
        'value'      => 'Reikšmė',
    ],
    'filters' => [
        'title' => 'Pavadinimas',
    ],
    'helpers' => [
        'test_value' => 'Įveskite reikšmę sąlygai išbandyti',
    ],
    'index' => [
        'title' => 'Nuolaidų sąlygos',
    ],
    'list' => [
        'title' => 'Sąlygų sąrašas',
    ],
    'messages' => [
        'created' => 'Sąlyga sėkmingai sukurta',
        'deleted' => 'Sąlyga sėkmingai ištrinta',
        'updated' => 'Sąlyga sėkmingai atnaujinta',
    ],
    'operators' => [
        'equals'       => 'Lygu',
        'greater_than' => 'Daugiau nei',
        'less_than'    => 'Mažiau nei',
        'contains'     => 'Yra',
        'not_contains' => 'Nėra',
    ],
    'quick_actions' => [
        'title' => 'Greiti veiksmai',
    ],
    'sections' => [
        'basic_info'   => 'Pagrindinė informacija',
        'translations' => 'Vertimai',
    ],
    'show' => [
        'subtitle' => 'Nuolaidų sąlygos informacija',
        'title'    => 'Sąlygos peržiūra',
    ],
    'stats' => [
        'active_conditions'        => 'Aktyvios sąlygos',
        'high_priority_conditions' => 'Aukšto prioriteto sąlygos',
        'inactive_conditions'      => 'Neaktyvios sąlygos',
        'position'                 => 'Pozicija',
        'priority'                 => 'Prioritetas',
        'status'                   => 'Būsena',
        'title'                    => 'Pavadinimas',
        'total_conditions'         => 'Iš viso sąlygų',
    ],
    'test' => [
        'title' => 'Sąlygos tikrinimas',
    ],
    'types' => [
        'cart_total'       => 'Krepšelio suma',
        'customer_group'   => 'Klientų grupė',
        'product_category' => 'Produkto kategorija',
        'product_quantity' => 'Produkto kiekis',
    ],
];
