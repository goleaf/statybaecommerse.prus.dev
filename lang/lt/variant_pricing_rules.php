<?php

declare(strict_types=1);

return [
    // Navigation and Labels
    'title'            => 'Varianto Kainodaros Taisyklės',
    'plural'           => 'Varianto Kainodaros Taisyklės',
    'single'           => 'Varianto Kainodaros Taisyklė',
    'navigation_label' => 'Kainodaros Taisyklės',
    'navigation_group' => 'Produktai',

    // Top-level labels consumed by the resource
    'basic_information'        => 'Pagrindinė informacija',
    'relationships'            => 'Ryšiai',
    'quantity_settings'        => 'Kiekio nustatymai',
    'status_settings'          => 'Būsenos nustatymai',
    'validity_period'          => 'Galiojimo laikotarpis',
    'description'              => 'Aprašymas',
    'name'                     => 'Taisyklės pavadinimas',
    'type'                     => 'Taisyklės tipas',
    'value'                    => 'Reikšmė',
    'priority'                 => 'Prioritetas',
    'product_variant'          => 'Produkto variantas',
    'customer_group'           => 'Klientų grupė',
    'min_quantity'             => 'Mažiausias kiekis',
    'max_quantity'             => 'Didžiausias kiekis',
    'is_active'                => 'Aktyvus',
    'is_cumulative'            => 'Kaupiamasis',
    'valid_from'               => 'Galioja nuo',
    'valid_until'              => 'Galioja iki',
    'created_at'               => 'Sukurta',
    'updated_at'               => 'Atnaujinta',
    'activate'                 => 'Aktyvuoti',
    'deactivate'               => 'Deaktyvuoti',
    'activate_selected'        => 'Aktyvuoti pasirinktus',
    'deactivate_selected'      => 'Deaktyvuoti pasirinktus',
    'activated_successfully'   => 'Kainodaros taisyklė sėkmingai aktyvuota',
    'deactivated_successfully' => 'Kainodaros taisyklė sėkmingai deaktyvuota',
    'bulk_activated_success'   => 'Pasirinktos taisyklės buvo aktyvuotos',
    'bulk_deactivated_success' => 'Pasirinktos taisyklės buvo deaktyvuotos',
    'active_only'              => 'Tik aktyvios',
    'inactive_only'            => 'Tik neaktyvios',
    'cumulative_only'          => 'Tik kaupiamosios',
    'non_cumulative_only'      => 'Tik nekaupiamosios',

    'types' => [
        'percentage' => 'Procentai',
        'fixed'      => 'Fiksuota suma',
        'tier'       => 'Pakopinė kainodara',
        'bulk'       => 'Didmeninė kainodara',
    ],

    // Tabs
    'tabs' => [
        'main'                 => 'Pagrindinė informacija',
        'basic_information'    => 'Pagrindinė informacija',
        'conditions'           => 'Sąlygos',
        'pricing_modifiers'    => 'Kainos modifikatoriai',
        'schedule'             => 'Tvarkaraštis',
        'all'                  => 'Visos taisyklės',
        'active'               => 'Aktyvios taisyklės',
        'size_based'           => 'Pagal dydį',
        'quantity_based'       => 'Pagal kiekį',
        'customer_group_based' => 'Pagal klientų grupę',
        'time_based'           => 'Pagal laiką',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'conditions'        => 'Taisyklės sąlygos',
        'pricing_modifiers' => 'Kainos modifikatoriai',
        'schedule'          => 'Tvarkaraščio nustatymai',
    ],

    // Fields
    'fields' => [
        'product'             => 'Produktas',
        'rule_name'           => 'Taisyklės pavadinimas',
        'rule_type'           => 'Taisyklės tipas',
        'priority'            => 'Prioritetas',
        'is_active'           => 'Aktyvus',
        'conditions'          => 'Sąlygos',
        'attribute'           => 'Atributas',
        'operator'            => 'Operatorius',
        'value'               => 'Reikšmė',
        'pricing_modifiers'   => 'Kainos modifikatoriai',
        'modifier_type'       => 'Modifikatoriaus tipas',
        'modifier_value'      => 'Modifikatoriaus reikšmė',
        'modifier_conditions' => 'Modifikatoriaus sąlygos',
        'starts_at'           => 'Pradžia',
        'ends_at'             => 'Pabaiga',
        'created_at'          => 'Sukurta',
    ],

    // Rule Types
    'rule_types' => [
        'size_based'           => 'Pagal dydį',
        'quantity_based'       => 'Pagal kiekį',
        'customer_group_based' => 'Pagal klientų grupę',
        'time_based'           => 'Pagal laiką',
    ],

    // Attributes
    'attributes' => [
        'size'         => 'Dydis',
        'variant_type' => 'Varianto tipas',
        'price'        => 'Kaina',
        'weight'       => 'Svoris',
    ],

    // Operators
    'operators' => [
        'equals'       => 'Lygu',
        'not_equals'   => 'Nelygu',
        'greater_than' => 'Daugiau nei',
        'less_than'    => 'Mažiau nei',
        'contains'     => 'Turi',
        'not_contains' => 'Neturi',
    ],

    // Modifier Types
    'modifier_types' => [
        'percentage'   => 'Procentai',
        'fixed_amount' => 'Fiksuota suma',
        'multiplier'   => 'Daugiklis',
    ],

    // Actions
    'actions' => [
        'add_condition'          => 'Pridėti sąlygą',
        'add_modifier'           => 'Pridėti modifikatorių',
        'add_modifier_condition' => 'Pridėti modifikatoriaus sąlygą',
        'activate'               => 'Aktyvuoti',
        'deactivate'             => 'Deaktyvuoti',
    ],

    // Messages
    'messages' => [
        'created_successfully'             => 'Kainodaros taisyklė sėkmingai sukurta',
        'created_successfully_description' => 'Kainodaros taisyklė buvo sukurta ir paruošta naudojimui',
        'updated_successfully'             => 'Kainodaros taisyklė sėkmingai atnaujinta',
        'updated_successfully_description' => 'Kainodaros taisyklė buvo atnaujinta su jūsų pakeitimais',
        'bulk_activate_success'            => 'Pasirinktos taisyklės buvo aktyvuotos',
        'bulk_deactivate_success'          => 'Pasirinktos taisyklės buvo deaktyvuotos',
    ],

    // Validation Messages
    'validation' => [
        'rule_name_required' => 'Taisyklės pavadinimas yra privalomas',
        'product_required'   => 'Produktas yra privalomas',
        'rule_type_required' => 'Taisyklės tipas yra privalomas',
        'priority_numeric'   => 'Prioritetas turi būti skaičius',
    ],

    // Help Text
    'help' => [
        'priority'          => 'Didesni skaičiai turi didesnį prioritetą',
        'conditions'        => 'Sąlygos, kurios turi būti įvykdytos, kad taisyklė būtų taikoma',
        'pricing_modifiers' => 'Kaip turi būti modifikuojama kaina, kai sąlygos įvykdytos',
        'starts_at'         => 'Kada taisyklė tampa aktyvi (neprivaloma)',
        'ends_at'           => 'Kada taisyklė tampa neaktyvi (neprivaloma)',
    ],
];
