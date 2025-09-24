<?php

return [
    'title' => 'Kolekcijų taisyklės',
    'plural' => 'Kolekcijų taisyklės',
    'single' => 'Kolekcijos taisyklė',
    'form' => [
        'tabs' => [
            'basic_information' => 'Pagrindinė informacija',
            'rule_details' => 'Taisyklės detalės',
        ],
        'sections' => [
            'basic_information' => 'Pagrindinė informacija',
            'rule_details' => 'Taisyklės detalės',
        ],
        'fields' => [
            'collection' => 'Kolekcija',
            'field' => 'Laukas',
            'operator' => 'Operatorius',
            'value' => 'Reikšmė',
            'position' => 'Pozicija',
            'collection_name' => 'Kolekcijos pavadinimas',
            'rule_description' => 'Taisyklės aprašymas',
            'start_position' => 'Pradžios pozicija',
            'created_at' => 'Sukurta',
        ],
    ],
    'operators' => [
        'equals' => 'Lygu',
        'not_equals' => 'Nelygu',
        'contains' => 'Turi',
        'not_contains' => 'Neturi',
        'starts_with' => 'Prasideda',
        'ends_with' => 'Baigiasi',
        'greater_than' => 'Daugiau nei',
        'less_than' => 'Mažiau nei',
        'greater_than_or_equal' => 'Daugiau arba lygu',
        'less_than_or_equal' => 'Mažiau arba lygu',
    ],
    'filters' => [
        'collection' => 'Kolekcija',
        'operator' => 'Operatorius',
        'created_at' => 'Sukurta',
        'recent' => 'Naujos (30 dienų)',
    ],
    'actions' => [
        'reorder' => 'Pertvarkyti',
        'reorder_bulk' => 'Pertvarkyti masiniškai',
    ],
    'notifications' => [
        'reordered_successfully' => 'Sėkmingai pertvarkyta',
        'bulk_reordered_successfully' => 'Sėkmingai pertvarkyta masiniškai',
    ],
];
