<?php

return [
    // Navigation
    'navigation' => [
        'partners' => 'Partneriai',
    ],

    // Models
    'models' => [
        'partner' => 'Partneris',
        'partners' => 'Partneriai',
    ],

    // Fields
    'fields' => [
        'name' => 'Pavadinimas',
        'code' => 'Kodas',
        'tier' => 'Lygių',
        'is_enabled' => 'Įjungtas',
        'contact_email' => 'Kontaktinis el. paštas',
        'contact_phone' => 'Kontaktinis telefonas',
        'discount_rate' => 'Nuolaidos procentas',
        'commission_rate' => 'Komisijos procentas',
        'logo' => 'Logotipas',
        'banner' => 'Baneris',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'contact_information' => 'Kontaktinė informacija',
        'financial_settings' => 'Finansiniai nustatymai',
        'media' => 'Medija',
    ],

    // Actions
    'actions' => [
        'create' => 'Sukurti',
        'view' => 'Peržiūrėti',
        'edit' => 'Redaguoti',
        'delete' => 'Ištrinti',
    ],

    // Help text
    'name_help' => 'Partnerio pavadinimas',
    'code_help' => 'Unikalus partnerio kodas',
    'tier_help' => 'Partnerio lygių',
    'contact_email_help' => 'Kontaktinis el. paštas',
    'contact_phone_help' => 'Kontaktinis telefonas',
    'discount_rate_help' => 'Nuolaidos procentas (0-100)',
    'commission_rate_help' => 'Komisijos procentas (0-100)',
    'logo_help' => 'Partnerio logotipas',
    'banner_help' => 'Partnerio baneris',
];
