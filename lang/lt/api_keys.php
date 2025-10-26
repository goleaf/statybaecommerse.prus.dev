<?php

declare(strict_types=1);

return [
    'navigation' => [
        'label'    => 'API raktai',
        'singular' => 'API raktas',
        'plural'   => 'API raktai',
    ],
    'sections' => [
        'details'     => 'Informacija',
        'credentials' => 'Kredencialai',
        'activity'    => 'Aktyvumas',
    ],
    'fields' => [
        'name'           => 'Pavadinimas',
        'scopes'         => 'Leidimai',
        'rate_limit'     => 'Užklausų limitas (per minutę)',
        'active'         => 'Aktyvus',
        'plain_text_key' => 'Slaptas raktas',
        'last_used_at'   => 'Paskutinį kartą naudotas',
        'created_at'     => 'Sukūrimo data',
        'updated_at'     => 'Atnaujinimo data',
    ],
    'placeholders' => [
        'name'       => 'Vidinis pavadinimas auditui',
        'rate_limit' => 'Neribota',
    ],
    'hints' => [
        'scopes'         => 'Pasirinkite, kokias prieigas suteiks šis raktas.',
        'rate_limit'     => 'Nurodykite leidžiamų užklausų skaičių per minutę. Palikite tuščią, jei riba netaikoma.',
        'generated_once' => 'Nukopijuokite slaptažodį dabar. Išėjus iš puslapio jis nebebus rodomas.',
    ],
    'filters' => [
        'active' => 'Būsena',
        'scope'  => 'Leidimas',
    ],
    'actions' => [
        'create'             => 'Sukurti API raktą',
        'regenerate'         => 'Sugeneruoti iš naujo',
        'confirm_regenerate' => 'Patvirtinti generavimą',
        'reveal'             => 'Rodyti',
        'hide'               => 'Slėpti',
        'copy'               => 'Kopijuoti',
        'close'              => 'Uždaryti',
    ],
    'notifications' => [
        'created'     => 'API raktas sėkmingai sukurtas.',
        'updated'     => 'API raktas sėkmingai atnaujintas.',
        'regenerated' => 'API raktas sėkmingai sugeneruotas iš naujo.',
    ],
    'modals' => [
        'reveal_title'           => 'API raktas „:name“',
        'reveal_description'     => 'Nukopijuokite ir saugiai išsaugokite slaptą raktą. Jis rodomas tik šį kartą.',
        'regenerate_description' => 'Naujas slaptas raktas iš karto panaikins ankstesnius prisijungimus.',
        'regenerate_warning'     => 'Esami klientai nebegalės prisijungti, kol neatnaujinsite jų naudojamo rakto.',
    ],
    'rate_limit' => [
        'unlimited' => 'Neribota',
    ],
    'scopes' => [
        'orders_read' => [
            'label'       => 'Užsakymai (skaitymas)',
            'description' => 'Suteikia prieigą peržiūrėti užsakymų informaciją.',
        ],
        'orders_write' => [
            'label'       => 'Užsakymai (rašymas)',
            'description' => 'Suteikia teisę kurti ar keisti užsakymus.',
        ],
        'products_read' => [
            'label'       => 'Produktai (skaitymas)',
            'description' => 'Suteikia prieigą prie produktų katalogo duomenų.',
        ],
        'products_write' => [
            'label'       => 'Produktai (rašymas)',
            'description' => 'Suteikia teisę kurti ar keisti produktų informaciją.',
        ],
        'customers_read' => [
            'label'       => 'Klientai (skaitymas)',
            'description' => 'Suteikia prieigą prie klientų įrašų.',
        ],
        'customers_write' => [
            'label'       => 'Klientai (rašymas)',
            'description' => 'Suteikia teisę kurti ar atnaujinti klientų duomenis.',
        ],
        'analytics_read' => [
            'label'       => 'Analitika (skaitymas)',
            'description' => 'Suteikia prieigą prie analitikos suvestinių ir rodiklių.',
        ],
    ],
];
