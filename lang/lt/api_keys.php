<?php

return [
    'navigation' => 'API raktai',
    'plural' => 'API raktai',
    'single' => 'API raktas',
    'fields' => [
        'name' => 'Pavadinimas',
        'rate_limit' => 'Užklausų riba',
        'scopes' => 'Leidimai',
        'is_active' => 'Aktyvus',
        'last_used_at' => 'Paskutinį kartą naudotas',
        'masked_key' => 'Užmaskuotas raktas',
        'key' => 'API raktas',
        'secret' => 'API paslaptis',
    ],
    'helpers' => [
        'rate_limit' => 'Palikite tuščią, jei norite neriboto skaičiaus užklausų. Įveskite skaičių, kad apribotumėte užklausas per minutę.',
        'scopes' => 'Pasirinkite, kokias prieigas suteikti šiam raktui.',
    ],
    'sections' => [
        'details' => 'Rakto informacija',
        'credentials' => 'Prisijungimo duomenys ir sauga',
    ],
    'messages' => [
        'no_key' => 'Neužmaskuotas raktas bus parodytas tik po išsaugojimo.',
        'unlimited' => 'Neribota',
        'requests_per_minute' => ':value užklausų/min',
        'copied' => 'Nukopijuota!',
        'secret_warning' => 'Laikykite šią paslaptį saugiai – ji nebebus rodoma pakartotinai.',
        'generate_after_save' => 'Išsaugokite įrašą, kad sugeneruotumėte raktą ir paslaptį. Jie bus parodyti tik vieną kartą.',
        'key_modal_hint' => 'Atskleiskite arba regeneruokite duomenis saugiai. Nukopijuokite juos iškart – langas užsidarys ir duomenys nebebus rodomi.',
    ],
    'actions' => [
        'reveal_key' => 'Rodyti raktą',
        'regenerate_key' => 'Sugeneruoti iš naujo',
        'copy' => 'Kopijuoti',
        'close' => 'Uždaryti',
        'reveal_secret' => 'Rodyti paslaptį',
        'hide_secret' => 'Slėpti paslaptį',
        'reactivate' => 'Aktyvuoti',
        'revoke' => 'Atšaukti',
    ],
    'modals' => [
        'reveal_key' => [
            'heading' => 'API prisijungimo duomenys',
        ],
    ],
    'notifications' => [
        'regenerated' => [
            'title' => 'API raktas sugeneruotas iš naujo',
            'body' => 'Naujas raktas: :key',
        ],
    ],
    'scopes' => [
        'read_products' => 'Peržiūrėti produktus',
        'write_products' => 'Tvarkyti produktus',
        'read_orders' => 'Peržiūrėti užsakymus',
        'manage_orders' => 'Tvarkyti užsakymus',
        'manage_customers' => 'Tvarkyti klientus',
        'access_analytics' => 'Peržiūrėti analitiką',
    ],
];
