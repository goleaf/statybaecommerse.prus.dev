<?php

return [
    'navigation' => [
        'label' => 'Kainos',
    ],

    'model' => [
        'singular' => 'Kaina',
        'plural' => 'Kainos',
    ],

    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'pricing' => 'Kainodara',
        'validity' => 'Galiojimo laikotarpis',
        'metadata' => 'Metaduomenys',
    ],

    'fields' => [
        'priceable' => 'Susietas objektas',
        'priceable_type' => 'Objekto tipas',
        'priceable_name' => 'Pavadinimas',
        'currency' => 'Valiuta',
        'type' => 'Kainos tipas',
        'amount' => 'Suma',
        'compare_amount' => 'Palyginamoji suma',
        'cost_amount' => 'Savikaina',
        'is_enabled' => 'Aktyvuota',
        'starts_at' => 'Pradžia',
        'ends_at' => 'Pabaiga',
        'metadata' => 'Metaduomenys',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    'filters' => [
        'priceable_type' => 'Objekto tipas',
        'currency' => 'Valiuta',
        'type' => 'Kainos tipas',
        'is_enabled' => 'Aktyvumo būsena',
        'active' => 'Aktyvios kainos',
    ],

    'priceable_types' => [
        'product' => 'Produktas',
        'variant' => 'Variantas',
    ],

    'types' => [
        'retail' => 'Mažmeninė',
        'wholesale' => 'Didmeninė',
        'special' => 'Speciali',
        'sale' => 'Išpardavimas',
    ],
];
