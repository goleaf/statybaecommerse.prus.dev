<?php

declare(strict_types=1);

return [
    'kpis' => [
        'orders_today'                        => 'Šiandienos užsakymai',
        'orders_today_description'            => 'Užsakymai gauti šiandien',
        'revenue_last_seven_days'             => 'Pajamos per 7 dienas',
        'revenue_last_seven_days_description' => 'Bendros pajamos per pastarąsias 7 dienas',
        'new_users_today'                     => 'Nauji vartotojai šiandien',
        'new_users_today_description'         => 'Nauji registruoti vartotojai šiandien',
        'low_stock_items'                     => 'Mažų atsargų prekės',
        'low_stock_items_description'         => 'Prekės su mažomis atsargomis',
    ],
    'actions' => [
        'heading'                  => 'Greiti veiksmai',
        'description'              => 'Dažnai naudojami administravimo veiksmai',
        'rebuild_search'           => 'Atstatyti paieškos indeksą',
        'rebuild_search_help'      => 'Atstatykite paieškos indeksą geresniam veikimui',
        'rebuild_search_heading'   => 'Atstatyti paieškos indeksą?',
        'rebuild_search_confirm'   => 'Šis veiksmas atstatys visą paieškos indeksą. Tai gali užtrukti kelias minutes.',
        'clear_cache'              => 'Išvalyti talpyklą',
        'clear_cache_help'         => 'Išvalykite aplikacijos talpyklą',
        'clear_cache_heading'      => 'Išvalyti talpyklą?',
        'clear_cache_confirm'      => 'Šis veiksmas išvalys visą aplikacijos talpyklą.',
        'run_minimal_seed'         => 'Paleisti minimalų duomenų užpildymą',
        'run_minimal_seed_help'    => 'Užpildykite duomenų bazę minimalia informacija',
        'run_minimal_seed_heading' => 'Paleisti duomenų užpildymą?',
        'run_minimal_seed_confirm' => 'Šis veiksmas užpildys duomenų bazę minimalia informacija testavimui.',
    ],
    'tables' => [
        'recent_orders'  => 'Paskutiniai užsakymai',
        'low_stock'      => 'Mažų atsargų prekės',
        'recent_errors'  => 'Paskutinės klaidos',
        'status_unknown' => 'Nežinoma būsena',
        'guest_customer' => 'Svečias',
    ],
    'errors' => [
        'metric_unavailable'      => 'Metrika nepasiekiama',
        'job'                     => 'Užduotis',
        'queue'                   => 'Eilė',
        'connection'              => 'Ryšys',
        'failed_at'               => 'Nepavyko',
        'exception'               => 'Klaida',
        'retry'                   => 'Bandyti dar kartą',
        'retry_placeholder'       => 'Pakartojimas šiuo metu nepasiekiamas',
        'no_failures'             => 'Klaidų nėra',
        'no_failures_description' => 'Šiuo metu nėra nepavykusių užduočių',
    ],
];
