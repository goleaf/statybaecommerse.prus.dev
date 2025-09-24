<?php

return [
    // Navigation
    'navigation' => [
        'partner_tiers' => 'Partnerių lygių',
    ],

    // Models
    'models' => [
        'partner_tier' => 'Partnerių lygių',
        'partner_tiers' => 'Partnerių lygių',
    ],

    // Fields
    'fields' => [
        'name' => 'Pavadinimas',
        'code' => 'Kodas',
        'is_enabled' => 'Įjungtas',
        'discount_rate' => 'Nuolaidos procentas',
        'commission_rate' => 'Komisijos procentas',
        'minimum_order_value' => 'Minimali užsakymo vertė',
        'benefits' => 'Privalumai',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'financial_settings' => 'Finansiniai nustatymai',
        'benefits' => 'Privalumai',
    ],

    // Actions
    'actions' => [
        'create' => 'Sukurti',
        'view' => 'Peržiūrėti',
        'edit' => 'Redaguoti',
        'delete' => 'Ištrinti',
    ],

    // Help text
    'name_help' => 'Lygių pavadinimas',
    'code_help' => 'Unikalus lygių kodas',
    'discount_rate_help' => 'Nuolaidos procentas (0-100)',
    'commission_rate_help' => 'Komisijos procentas (0-100)',
    'minimum_order_value_help' => 'Minimali užsakymo vertė (€)',
    'benefits_help' => 'Lygių privalumai',
];
