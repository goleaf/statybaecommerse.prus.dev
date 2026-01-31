<?php

declare(strict_types=1);

return [
    'messages' => [
        'condition_matches'        => 'Sąlyga sutampa',
        'condition_does_not_match' => 'Sąlyga nesutampa',
    ],
    'types' => [
        'product'         => 'Produktas',
        'category'        => 'Kategorija',
        'brand'           => 'Prekės ženklas',
        'collection'      => 'Kolekcija',
        'attribute_value' => 'Atributo reikšmė',
        'cart_total'      => 'Krepšelio suma',
        'item_qty'        => 'Prekių kiekis',
        'channel'         => 'Kanalas',
        'currency'        => 'Valiuta',
        'customer_group'  => 'Klientų grupė',
        'user'            => 'Naudotojas',
        'partner_tier'    => 'Partnerio lygis',
        'first_order'     => 'Pirmas užsakymas',
        'day_time'        => 'Diena ir laikas',
        'custom_script'   => 'Programinis kodas',
    ],
    'operators' => [
        'equals_to'             => 'Lygu',
        'not_equals_to'         => 'Nelygu',
        'less_than'             => 'Mažiau nei',
        'greater_than'          => 'Daugiau nei',
        'less_than_or_equal'    => 'Mažiau arba lygu',
        'greater_than_or_equal' => 'Daugiau arba lygu',
        'starts_with'           => 'Prasideda',
        'ends_with'             => 'Baigiasi',
        'contains'              => 'Turi',
        'not_contains'          => 'Neturi',
        'in_array'              => 'Yra sąraše',
        'not_in_array'          => 'Nėra sąraše',
        'regex'                 => 'Regex atitiktis',
        'not_regex'             => 'Regex neatitiktis',
    ],
    'index' => [
        'title' => 'Nuolaidų sąlygos',
    ],
    'actions' => [
        'test_all'       => 'Tikrinti visas',
        'test_condition' => 'Tikrinti sąlygą',
        'view_discount'  => 'Peržiūrėti nuolaidą',
    ],
    'filters' => [
        'title' => 'Filtrai',
    ],
    'fields' => [
        'type'       => 'Tipas',
        'discount'   => 'Nuolaida',
        'operator'   => 'Operatorius',
        'value'      => 'Reikšmė',
        'priority'   => 'Prioritetas',
        'condition'  => 'Sąlyga',
        'test_value' => 'Testinė reikšmė',
        'position'   => 'Pozicija',
        'is_active'  => 'Aktyvi',
        'created_at' => 'Sukurta',
        'metadata'   => 'Metaduomenys',
    ],
    'stats' => [
        'total_conditions'         => 'Iš viso sąlygų',
        'active_conditions'        => 'Aktyvios sąlygos',
        'inactive_conditions'      => 'Neaktyvios sąlygos',
        'high_priority_conditions' => 'Aukšto prioriteto',
        'title'                    => 'Statistika',
        'priority'                 => 'Prioritetas',
        'position'                 => 'Pozicija',
        'status'                   => 'Būsena',
    ],
    'list' => [
        'title' => 'Sąlygų sąrašas',
    ],
    'empty' => [
        'title'       => 'Sąlygų nerasta',
        'description' => 'Šiai nuolaidai netaikomos jokios sąlygos.',
    ],
    'test' => [
        'title' => 'Sąlygos tikrinimas',
    ],
    'show' => [
        'title'    => 'Sąlygos informacija',
        'subtitle' => 'Išsami informacija apie sąlygą',
    ],
    'sections' => [
        'basic_info'   => 'Pagrindinė informacija',
        'translations' => 'Vertimai',
    ],
    'quick_actions' => [
        'title' => 'Greiti veiksmai',
    ],
    'helpers' => [
        'test_value' => 'Įveskite reikšmę sąlygos patikrinimui',
    ],
];
