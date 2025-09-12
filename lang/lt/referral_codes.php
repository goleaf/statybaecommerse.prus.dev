<?php

return [
    'navigation_label' => 'Referral kodai',
    'model_label' => 'Referral kodas',
    'plural_model_label' => 'Referral kodai',

    'sections' => [
        'code_information' => 'Kodo informacija',
        'translatable_content' => 'Verčiamas turinys',
        'usage_settings' => 'Naudojimo nustatymai',
        'reward_settings' => 'Atlygio nustatymai',
        'conditions' => 'Sąlygos',
        'metadata' => 'Papildomi duomenys',
        'code_details' => 'Kodo detalės',
    ],

    'fields' => [
        'user' => 'Vartotojas',
        'code' => 'Referral kodas',
        'is_active' => 'Aktyvus',
        'expires_at' => 'Galioja iki',
        'title' => 'Pavadinimas',
        'description' => 'Aprašymas',
        'usage_limit' => 'Naudojimo limitas',
        'usage_count' => 'Naudojimo skaičius',
        'source' => 'Šaltinis',
        'tags' => 'Žymės',
        'reward_amount' => 'Atlygio suma',
        'reward_type' => 'Atlygio tipas',
        'campaign' => 'Kampanija',
        'conditions' => 'Sąlygos',
        'metadata' => 'Papildomi duomenys',
        'created_at' => 'Sukurta',
        'referral_url' => 'Referral nuoroda',
        'usage_percentage' => 'Naudojimo procentas',
    ],

    'helpers' => [
        'code' => 'Palikite tuščią, kad automatiškai sugeneruotų',
        'expires_at' => 'Palikite tuščią, kad neturėtų galiojimo pabaigos',
        'usage_limit' => 'Palikite tuščią, kad neturėtų limito',
        'conditions' => 'JSON formatu sąlygos, kurias turi atitikti vartotojas',
    ],

    'sources' => [
        'admin' => 'Administratorius',
        'user' => 'Vartotojas',
        'api' => 'API',
        'import' => 'Importas',
    ],

    'reward_types' => [
        'percentage' => 'Procentai',
        'fixed' => 'Fiksuota suma',
        'points' => 'Taškai',
    ],

    'filters' => [
        'active' => 'Aktyvūs',
        'expired' => 'Pasibaigę',
        'expires_soon' => 'Greitai pasibaigiantys (7 dienos)',
        'with_usage_limit' => 'Su naudojimo limitu',
        'by_source' => 'Pagal šaltinį',
        'by_reward_type' => 'Pagal atlygio tipą',
        'select_source' => 'Pasirinkite šaltinį',
        'select_reward_type' => 'Pasirinkite atlygio tipą',
    ],

    'actions' => [
        'deactivate' => 'Deaktyvuoti',
        'activate' => 'Aktyvuoti',
        'deactivate_selected' => 'Deaktyvuoti pasirinktus',
        'activate_selected' => 'Aktyvuoti pasirinktus',
        'copy_url' => 'Kopijuoti nuorodą',
        'view_stats' => 'Peržiūrėti statistiką',
    ],

    'notifications' => [
        'url_copied' => 'Nuoroda nukopijuota į iškarpinę',
    ],

    'stats' => [
        'total_codes' => 'Iš viso kodų',
        'total_codes_description' => 'Bendras referral kodų skaičius',
        'active_codes' => 'Aktyvūs kodai',
        'active_codes_description' => 'Šiuo metu aktyvūs kodai',
        'expired_codes' => 'Pasibaigę kodai',
        'expired_codes_description' => 'Pasibaigę arba deaktyvuoti kodai',
        'total_usage' => 'Bendras naudojimas',
        'total_usage_description' => 'Bendras visų kodų naudojimo skaičius',
    ],

    'charts' => [
        'usage_over_time' => 'Naudojimas per laiką',
        'usage_label' => 'Naudojimo skaičius',
    ],

    'widgets' => [
        'top_codes' => 'Populiariausi referral kodai',
    ],

    'unlimited' => 'Neribotai',
    'never_expires' => 'Niekada nesibaigia',
    'no_title' => 'Nėra pavadinimo',
    'no_description' => 'Nėra aprašymo',
    'no_reward' => 'Nėra atlygio',
    'no_campaign' => 'Nėra kampanijos',

    'pages' => [
        'index' => [
            'title' => 'Mano referral kodai',
            'your_codes' => 'Jūsų kodai',
            'no_codes' => 'Nėra referral kodų',
            'no_codes_description' => 'Pradėkite kurdami savo pirmąjį referral kodą.',
        ],
        'create' => [
            'title' => 'Sukurti referral kodą',
        ],
        'edit' => [
            'title' => 'Redaguoti referral kodą',
        ],
        'show' => [
            'title' => 'Referral kodas',
        ],
    ],

    'status' => [
        'active' => 'Aktyvus',
        'inactive' => 'Neaktyvus',
    ],

    'messages' => [
        'created_successfully' => 'Referral kodas sėkmingai sukurtas',
        'updated_successfully' => 'Referral kodas sėkmingai atnaujintas',
        'deleted_successfully' => 'Referral kodas sėkmingai ištrintas',
        'activated_successfully' => 'Referral kodas sėkmingai aktyvuotas',
        'deactivated_successfully' => 'Referral kodas sėkmingai deaktyvuotas',
        'url_copied' => 'Nuoroda nukopijuota į iškarpinę',
    ],
];
