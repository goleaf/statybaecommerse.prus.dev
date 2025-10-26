<?php

declare(strict_types=1);

return [
    'title'    => 'Rekomendacijų statistika',
    'plural'   => 'Rekomendacijų statistika',
    'single'   => 'Rekomendacijų statistikos įrašas',
    'sections' => [
        'basic_info'                  => 'Pagrindinė informacija',
        'basic_info_description'      => 'Sekite, kam priklauso statistika ir kada ji užfiksuota.',
        'referral_stats'              => 'Rekomendacijų rezultatai',
        'referral_stats_description'  => 'Visų rekomendacijų baigčių skaičiai pasirinktame laikotarpyje.',
        'financial_stats'             => 'Finansinis poveikis',
        'financial_stats_description' => 'Gautų atlygių ir suteiktų nuolaidų sumos.',
        'advanced'                    => 'Išplėstinės detalės',
        'advanced_description'        => 'Saugo papildomus šio statistikos įrašo metaduomenis.',
        'timestamps'                  => 'Laiko žymos',
    ],
    'fields' => [
        'user_id'               => 'Naudotojas',
        'user_name'             => 'Naudotojas',
        'date'                  => 'Data',
        'total_referrals'       => 'Visos rekomendacijos',
        'completed_referrals'   => 'Įvykdytos rekomendacijos',
        'pending_referrals'     => 'Laukiamos rekomendacijos',
        'total_rewards_earned'  => 'Gauti atlygiai',
        'total_discounts_given' => 'Suteiktos nuolaidos',
        'metadata'              => 'Metaduomenys',
        'metadata_key'          => 'Raktas',
        'metadata_value'        => 'Reikšmė',
        'created_at'            => 'Sukurta',
        'updated_at'            => 'Atnaujinta',
    ],
    'filters' => [
        'user'          => 'Naudotojas',
        'date_range'    => 'Datos intervalas',
        'from_date'     => 'Nuo datos',
        'until_date'    => 'Iki datos',
        'has_referrals' => 'Turi rekomendacijų',
        'has_rewards'   => 'Turi atlygių',
    ],
    'actions' => [
        'add_metadata'      => 'Pridėti metaduomenis',
        'refresh_stats'     => 'Atnaujinti statistiką',
        'refresh_all_stats' => 'Atnaujinti visas statistikas',
    ],
    'notifications' => [
        'stats_refreshed'     => 'Rekomendacijų statistika sėkmingai atnaujinta.',
        'all_stats_refreshed' => 'Visos rekomendacijų statistikos sėkmingai atnaujintos.',
    ],
    'placeholders' => [
        'no_metadata' => 'Metaduomenų dar nėra.',
    ],
];
