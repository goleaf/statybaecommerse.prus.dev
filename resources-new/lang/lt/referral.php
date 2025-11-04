<?php

declare(strict_types=1);

return [
    'nav' => [
        'group' => 'Rekomendacijos',
    ],
    'resource' => [
        'referral_code' => [
            'section' => [
                'code_details' => 'Kodo informacija',
            ],
        ],
    ],
    'form' => [
        'user' => 'Vartotojas',
        'code' => 'Kodą',
        'title' => 'Pavadinimas',
        'description' => 'Aprašymas',
        'is_active' => 'Aktyvus',
        'expires_at' => 'Galiojimo pabaiga',
        'usage_limit' => 'Naudojimo limitas',
        'usage_count' => 'Naudojimo skaičius',
        'reward_amount' => 'Atlygio suma',
        'reward_type' => 'Atlygio tipas',
        'campaign_id' => 'Kampanija',
        'source' => 'Šaltinis',
        'conditions' => 'Sąlygos (JSON)',
        'conditions_key' => 'Raktas',
        'conditions_value' => 'Reikšmė',
        'conditions_add' => 'Pridėti sąlygą',
        'tags' => 'Žymos (JSON)',
        'tags_key' => 'Žyma',
        'tags_value' => 'Reikšmė',
        'tags_add' => 'Pridėti žymą',
        'metadata' => 'Metaduomenys (JSON)',
        'metadata_key' => 'Raktas',
        'metadata_value' => 'Reikšmė',
        'metadata_add' => 'Pridėti metaduomenį',
    ],
    'columns' => [
        'is_active' => 'Aktyvus',
    ],
    'filters' => [
        'is_active' => 'Aktyvus',
        'reward_type' => 'Atlygio tipas',
        'user' => 'Vartotojas',
        'campaign_id' => 'Kampanija',
    ],
    'reward_types' => [
        'discount' => 'Nuolaida',
        'credit' => 'Kreditas',
        'points' => 'Taškai',
        'gift' => 'Dovana',
    ],
];
