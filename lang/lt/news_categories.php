<?php

return [
    'fields' => [
        'name' => 'Pavadinimas',
        'slug' => 'URL nuoroda',
        'description' => 'Aprašymas',
        'parent_id' => 'Tėvinė kategorija',
        'parent' => 'Tėvinė kategorija',
        'sort_order' => 'Rikiavimo tvarka',
        'color' => 'Spalva',
        'icon' => 'Ikona',
        'is_visible' => 'Matoma',
        'news_count' => 'Naujienų skaičius',
        'children_count' => 'Poteklės kategorijų skaičius',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],
    'filters' => [
        'parent' => 'Tėvinė kategorija',
        'is_visible' => 'Matoma',
    ],
    'actions' => [
        'create' => 'Sukurti kategoriją',
        'edit' => 'Redaguoti kategoriją',
        'delete' => 'Ištrinti kategoriją',
        'view' => 'Peržiūrėti kategoriją',
    ],
    'messages' => [
        'created' => 'Kategorija sėkmingai sukurta',
        'updated' => 'Kategorija sėkmingai atnaujinta',
        'deleted' => 'Kategorija sėkmingai ištrinta',
    ],
    'sections' => [
        'category_information' => 'Kategorijos informacija',
        'hierarchy_display' => 'Hierarchija ir rodymas',
        'visibility' => 'Matomumas',
        'statistics' => 'Statistika',
        'category_details' => 'Kategorijos duomenys',
        'display_settings' => 'Rodymo nustatymai',
    ],
];
