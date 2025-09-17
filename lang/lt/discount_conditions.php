<?php

return [
    'sections' => [
        'basic_info' => 'Pagrindinė informacija',
        'translations' => 'Vertimai',
        'advanced' => 'Papildomi nustatymai',
    ],

    'fields' => [
        'discount' => 'Nuolaida',
        'type' => 'Tipas',
        'operator' => 'Operatorius',
        'value' => 'Reikšmė',
        'position' => 'Pozicija',
        'priority' => 'Prioritetas',
        'is_active' => 'Aktyvus',
        'name' => 'Pavadinimas',
        'description' => 'Aprašymas',
        'metadata' => 'Metaduomenys',
        'metadata_key' => 'Raktas',
        'metadata_value' => 'Reikšmė',
        'locale' => 'Kalba',
        'translations' => 'Vertimai',
        'condition' => 'Sąlyga',
        'test_value' => 'Testo reikšmė',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    'types' => [
        'product' => 'Prekė',
        'category' => 'Kategorija',
        'brand' => 'Prekės ženklas',
        'collection' => 'Kolekcija',
        'attribute_value' => 'Atributo reikšmė',
        'cart_total' => 'Krepšelio suma',
        'item_qty' => 'Prekių kiekis',
        'zone' => 'Zona',
        'channel' => 'Kanalas',
        'currency' => 'Valiuta',
        'customer_group' => 'Klientų grupė',
        'user' => 'Vartotojas',
        'partner_tier' => 'Partnerio lygis',
        'first_order' => 'Pirmasis užsakymas',
        'day_time' => 'Dienos laikas',
        'custom_script' => 'Pritaikytas scenarijus',
    ],

    'operators' => [
        'equals_to' => 'Lygu',
        'not_equals_to' => 'Nelygu',
        'less_than' => 'Mažiau nei',
        'greater_than' => 'Daugiau nei',
        'less_than_or_equal' => 'Mažiau arba lygu',
        'greater_than_or_equal' => 'Daugiau arba lygu',
        'starts_with' => 'Prasideda',
        'ends_with' => 'Baigiasi',
        'contains' => 'Turi',
        'not_contains' => 'Neturi',
        'in_array' => 'Yra sąraše',
        'not_in_array' => 'Nėra sąraše',
        'regex' => 'Reguliarus išraiška',
        'not_regex' => 'Ne reguliarus išraiška',
    ],

    'helpers' => [
        'position' => 'Sąlygų vykdymo tvarka',
        'priority' => 'Sąlygos svarba (didesnis skaičius = aukštesnis prioritetas)',
        'metadata' => 'Papildomi duomenys JSON formatu',
        'numeric_value' => 'Įveskite skaitinę reikšmę',
        'string_value' => 'Įveskite tekstinę reikšmę',
        'array_value' => 'Įveskite reikšmes per kablelį',
        'regex_value' => 'Įveskite reguliarų išraišką',
        'general_value' => 'Įveskite reikšmę pagal sąlygos tipą',
        'test_value' => 'Įveskite reikšmę, kurią norite patikrinti',
    ],

    'actions' => [
        'test_condition' => 'Tikrinti sąlygą',
        'activate' => 'Aktyvuoti',
        'deactivate' => 'Deaktyvuoti',
        'set_priority' => 'Nustatyti prioritetą',
        'view' => 'Peržiūrėti',
    ],

    'filters' => [
        'high_priority' => 'Aukštas prioritetas',
        'low_priority' => 'Žemas prioritetas',
        'numeric_conditions' => 'Skaitinės sąlygos',
        'string_conditions' => 'Tekstinės sąlygos',
    ],

    'tabs' => [
        'all' => 'Visi',
        'active' => 'Aktyvūs',
        'inactive' => 'Neaktyvūs',
        'high_priority' => 'Aukštas prioritetas',
        'numeric' => 'Skaitinės',
        'string' => 'Tekstinės',
    ],

    'stats' => [
        'total_conditions' => 'Iš viso sąlygų',
        'total_conditions_description' => 'Bendras sąlygų skaičius',
        'active_conditions' => 'Aktyvios sąlygos',
        'active_conditions_description' => 'Šiuo metu aktyvios',
        'inactive_conditions' => 'Neaktyvios sąlygos',
        'inactive_conditions_description' => 'Šiuo metu neaktyvios',
        'high_priority_conditions' => 'Aukšto prioriteto',
        'high_priority_conditions_description' => 'Prioritetas > 5',
    ],

    'charts' => [
        'conditions_by_type' => 'Sąlygos pagal tipą',
    ],

    'messages' => [
        'condition_matches' => 'Sąlyga atitinka nurodytą reikšmę',
        'condition_does_not_match' => 'Sąlyga neatitinka nurodytos reikšmės',
    ],

    'notifications' => [
        'created' => 'Sąlyga sėkmingai sukurta',
        'updated' => 'Sąlyga sėkmingai atnaujinta',
        'deleted' => 'Sąlyga sėkmingai ištrinta',
    ],
];
