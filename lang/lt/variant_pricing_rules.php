<?php

declare(strict_types=1);

return [
    // Navigation and Labels
    'title' => 'Varianto Kainodaros Taisyklės',
    'plural' => 'Varianto Kainodaros Taisyklės',
    'single' => 'Varianto Kainodaros Taisyklė',
    'navigation_label' => 'Kainodaros Taisyklės',
    'navigation_group' => 'Produktai',

    // Tabs
    'tabs' => [
        'main' => 'Pagrindinė Informacija',
        'basic_information' => 'Pagrindinė Informacija',
        'conditions' => 'Sąlygos',
        'pricing_modifiers' => 'Kainos Modifikatoriai',
        'schedule' => 'Tvarkaraštis',
        'all' => 'Visos Taisyklės',
        'active' => 'Aktyvios Taisyklės',
        'size_based' => 'Pagal Dydį',
        'quantity_based' => 'Pagal Kiekį',
        'customer_group_based' => 'Pagal Klientų Grupę',
        'time_based' => 'Pagal Laiką',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė Informacija',
        'conditions' => 'Taisyklės Sąlygos',
        'pricing_modifiers' => 'Kainos Modifikatoriai',
        'schedule' => 'Tvarkaraščio Nustatymai',
    ],

    // Fields
    'fields' => [
        'product' => 'Produktas',
        'rule_name' => 'Taisyklės Pavadinimas',
        'rule_type' => 'Taisyklės Tipas',
        'priority' => 'Prioritetas',
        'is_active' => 'Aktyvus',
        'conditions' => 'Sąlygos',
        'attribute' => 'Atributas',
        'operator' => 'Operatorius',
        'value' => 'Reikšmė',
        'pricing_modifiers' => 'Kainos Modifikatoriai',
        'modifier_type' => 'Modifikatoriaus Tipas',
        'modifier_value' => 'Modifikatoriaus Reikšmė',
        'modifier_conditions' => 'Modifikatoriaus Sąlygos',
        'starts_at' => 'Pradžia',
        'ends_at' => 'Pabaiga',
        'created_at' => 'Sukurta',
    ],

    // Rule Types
    'rule_types' => [
        'size_based' => 'Pagal Dydį',
        'quantity_based' => 'Pagal Kiekį',
        'customer_group_based' => 'Pagal Klientų Grupę',
        'time_based' => 'Pagal Laiką',
    ],

    // Attributes
    'attributes' => [
        'size' => 'Dydis',
        'variant_type' => 'Varianto Tipas',
        'price' => 'Kaina',
        'weight' => 'Svoris',
    ],

    // Operators
    'operators' => [
        'equals' => 'Lygu',
        'not_equals' => 'Nelygu',
        'greater_than' => 'Daugiau nei',
        'less_than' => 'Mažiau nei',
        'contains' => 'Turi',
        'not_contains' => 'Neturi',
    ],

    // Modifier Types
    'modifier_types' => [
        'percentage' => 'Procentai',
        'fixed_amount' => 'Fiksuota Suma',
        'multiplier' => 'Daugiklis',
    ],

    // Actions
    'actions' => [
        'add_condition' => 'Pridėti Sąlygą',
        'add_modifier' => 'Pridėti Modifikatorių',
        'add_modifier_condition' => 'Pridėti Modifikatoriaus Sąlygą',
        'activate' => 'Aktyvuoti',
        'deactivate' => 'Deaktyvuoti',
    ],

    // Messages
    'messages' => [
        'created_successfully' => 'Kainodaros taisyklė sėkmingai sukurta',
        'created_successfully_description' => 'Kainodaros taisyklė buvo sukurta ir paruošta naudojimui',
        'updated_successfully' => 'Kainodaros taisyklė sėkmingai atnaujinta',
        'updated_successfully_description' => 'Kainodaros taisyklė buvo atnaujinta su jūsų pakeitimais',
        'bulk_activate_success' => 'Pasirinktos taisyklės buvo aktyvuotos',
        'bulk_deactivate_success' => 'Pasirinktos taisyklės buvo deaktyvuotos',
    ],

    // Validation Messages
    'validation' => [
        'rule_name_required' => 'Taisyklės pavadinimas yra privalomas',
        'product_required' => 'Produktas yra privalomas',
        'rule_type_required' => 'Taisyklės tipas yra privalomas',
        'priority_numeric' => 'Prioritetas turi būti skaičius',
    ],

    // Help Text
    'help' => [
        'priority' => 'Didesni skaičiai turi didesnį prioritetą',
        'conditions' => 'Sąlygos, kurios turi būti įvykdytos, kad taisyklė būtų taikoma',
        'pricing_modifiers' => 'Kaip turi būti modifikuojama kaina, kai sąlygos įvykdytos',
        'starts_at' => 'Kada taisyklė tampa aktyvi (neprivaloma)',
        'ends_at' => 'Kada taisyklė tampa neaktyvi (neprivaloma)',
    ],
];
