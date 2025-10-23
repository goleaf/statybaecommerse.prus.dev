<?php

declare(strict_types=1);

return [
    'title'  => 'Nuolaidos sąlygos',
    'plural' => 'Nuolaidos sąlygos',
    'single' => 'Nuolaidos sąlyga',

    'tabs'               => 'Skirtukai',
    'basic_information'  => 'Pagrindinė informacija',
    'condition_settings' => 'Sąlygų nustatymai',
    'targeting'          => 'Taikymas',
    'settings'           => 'Nustatymai',

    'name'        => 'Pavadinimas',
    'description' => 'Aprašymas',
    'discount'    => 'Nuolaida',
    'type'        => 'Tipas',
    'operator'    => 'Operatorius',
    'value'       => 'Reikšmė',
    'priority'    => 'Prioritetas',
    'position'    => 'Pozicija',
    'products'    => 'Produktai',
    'categories'  => 'Kategorijos',
    'is_active'   => 'Aktyvus',
    'is_required' => 'Privalomas',
    'metadata'    => 'Metaduomenys',

    'value_help'    => 'Sąlygos reikšmė, priklausomai nuo tipo',
    'priority_help' => 'Sąlygų vykdymo prioritetas (0 = aukščiausias)',
    'metadata_help' => 'Papildomi metaduomenys JSON formatu',

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
        'user'            => 'Vartotojas',
        'partner_tier'    => 'Partnerio lygis',
        'first_order'     => 'Pirmas užsakymas',
        'day_time'        => 'Diena/laikas',
        'custom_script'   => 'Individualus skriptas',
    ],

    'operators' => [
        'equals_to'             => 'Lygus',
        'not_equals_to'         => 'Nelygus',
        'less_than'             => 'Mažiau nei',
        'greater_than'          => 'Daugiau nei',
        'less_than_or_equal'    => 'Mažiau arba lygus',
        'greater_than_or_equal' => 'Daugiau arba lygus',
        'starts_with'           => 'Prasideda',
        'ends_with'             => 'Baigiasi',
        'contains'              => 'Turi',
        'not_contains'          => 'Neturi',
        'in_array'              => 'Yra masyve',
        'not_in_array'          => 'Nėra masyve',
        'regex'                 => 'Regex atitikmuo',
        'not_regex'             => 'Regex neatitikmuo',
    ],

    'products_count'   => 'Produktų skaičius',
    'categories_count' => 'Kategorijų skaičius',
    'created_at'       => 'Sukurta',
    'updated_at'       => 'Atnaujinta',

    'active_only'   => 'Tik aktyvūs',
    'inactive_only' => 'Tik neaktyvūs',
    'required_only' => 'Tik privalomi',
    'optional_only' => 'Tik neprivalomi',

    'activate'                 => 'Aktyvuoti',
    'deactivate'               => 'Deaktyvuoti',
    'activated_successfully'   => 'Sėkmingai aktyvuota',
    'deactivated_successfully' => 'Sėkmingai deaktyvuota',

    'activate_selected'        => 'Aktyvuoti pasirinktus',
    'deactivate_selected'      => 'Deaktyvuoti pasirinktus',
    'bulk_activated_success'   => 'Sėkmingai aktyvuoti pasirinkti įrašai',
    'bulk_deactivated_success' => 'Sėkmingai deaktyvuoti pasirinkti įrašai',
    'set_priority' => 'Nustatyti prioritetą',

    'boolean_yes' => 'Taip',
    'boolean_no' => 'Ne',

    'charts' => [
        'conditions_by_type' => 'Sąlygos pagal tipą',
    ],

    'stats' => [
        'total_conditions' => 'Iš viso sąlygų',
        'total_conditions_description' => 'Bendras sukonfigūruotų sąlygų skaičius.',
        'active_conditions' => 'Aktyvios sąlygos',
        'active_conditions_description' => 'Šiuo metu įjungtos sąlygos.',
        'inactive_conditions' => 'Neaktyvios sąlygos',
        'inactive_conditions_description' => 'Šiuo metu išjungtos sąlygos.',
        'top_condition_type' => 'Dažniausias tipas',
        'top_condition_type_description' => 'Dažniausiai naudojamas sąlygos tipas.',
        'type_usage' => '{1} :count sąlyga|{2} :count sąlygos|[3,*] :count sąlygų',
        'no_data' => 'Nėra duomenų',
    ],

    'widgets' => [
        'recent_conditions' => 'Naujausios nuolaidų sąlygos',
    ],
];
