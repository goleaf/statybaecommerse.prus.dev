<?php

declare(strict_types=1);

return [
    // Pagrindiniai pavadinimai
    'plural'         => 'Miestai',
    'single'         => 'Miestas',
    'name'           => 'Pavadinimas',
    'code'           => 'Kodas',
    'slug'           => 'URL dalis',
    'description'    => 'Aprašymas',
    'country'        => 'Šalis',
    'city'           => 'Miestas',
    'state_province' => 'Valstija / provincija',
    'country_code'   => 'Šalies kodas',
    'postal_code'    => 'Pašto kodas',

    // Koordinatės
    'latitude'    => 'Platuma',
    'longitude'   => 'Ilguma',
    'coordinates' => 'Koordinatės',

    // Demografija
    'population' => 'Gyventojų skaičius',
    'area'       => 'Plotas (km²)',
    'density'    => 'Tankis (/km²)',
    'elevation'  => 'Aukštis (m)',
    'timezone'   => 'Laiko juosta',

    // Lokalizacija
    'currency_code' => 'Valiutos kodas',
    'language_code' => 'Kalbos kodas',
    'phone_code'    => 'Telefono kodas',

    // Hierarchija
    'parent_city' => 'Tėvinis miestas',
    'level'       => 'Lygis',

    // Nustatymai
    'is_active'  => 'Aktyvus',
    'is_capital' => 'Sostinė',
    'is_default' => 'Numatytasis',
    'sort_order' => 'Rikiavimo tvarka',
    'type'       => 'Tipas',

    // Tipai
    'types' => [
        'metropolitan' => 'Metropolinis',
        'urban'        => 'Miesto',
        'rural'        => 'Kaimo',
        'suburban'     => 'Priemiestinis',
        'industrial'   => 'Pramoninis',
        'tourist'      => 'Turistinis',
    ],

    // Lygiai
    'levels' => [
        0 => 'Miestas',
        1 => 'Rajonas',
        2 => 'Mikrorajonas',
        3 => 'Priemiestis',
        4 => 'Kaimas',
        5 => 'Miestelis',
    ],

    // Skyriai
    'basic_information' => 'Pagrindinė informacija',
    'coordinates'       => 'Koordinatės',
    'demographics'      => 'Demografiniai duomenys',
    'localization'      => 'Lokalizacija',
    'hierarchy'         => 'Hierarchija',
    'settings'          => 'Nustatymai',

    // Pagalbos tekstai
    'slug_help'          => 'URL formato pavadinimas',
    'code_help'          => 'Trumpas unikalus identifikatorius',
    'latitude_help'      => 'Platumos koordinatė (-90 iki 90)',
    'longitude_help'     => 'Ilgumos koordinatė (-180 iki 180)',
    'population_help'    => 'Gyventojų skaičius',
    'area_help'          => 'Plotas kvadratiniais kilometrais',
    'density_help'       => 'Gyventojų tankis viename kvadratiniame kilometre',
    'elevation_help'     => 'Aukštis virš jūros lygio (metrais)',
    'timezone_help'      => 'Laiko juostos identifikatorius (pvz., Europe/Vilnius)',
    'currency_code_help' => 'ISO 4217 valiutos kodas',
    'language_code_help' => 'ISO 639 kalbos kodas',
    'phone_code_help'    => 'Tarptautinis telefono kodas',
    'parent_city_help'   => 'Tėvinis miestas hierarchijai sudaryti',
    'level_help'         => 'Hierarchijos lygis (0–10)',
    'sort_order_help'    => 'Atvaizdavimo eilė (mažesni skaičiai rodomi pirmiau)',
    'type_help'          => 'Miesto klasifikacijos tipas',

    // Veiksmai
    'activate'       => 'Aktyvuoti',
    'deactivate'     => 'Išjungti',
    'set_capital'    => 'Nustatyti sostine',
    'remove_capital' => 'Pašalinti sostinės statusą',
    'set_default'    => 'Nustatyti numatytuoju',
    'remove_default' => 'Pašalinti numatytojo statusą',

    // Masiniai veiksmai
    'activate_selected'       => 'Aktyvuoti pasirinktus',
    'deactivate_selected'     => 'Išjungti pasirinktus',
    'set_capital_selected'    => 'Pažymėtus nustatyti sostinėmis',
    'remove_capital_selected' => 'Pažymėtiems pašalinti sostinės statusą',

    // Filtrai
    'active_only'      => 'Tik aktyvūs',
    'inactive_only'    => 'Tik neaktyvūs',
    'capital_only'     => 'Tik sostinės',
    'non_capital_only' => 'Tik ne sostinės',
    'default_only'     => 'Tik numatytieji',
    'non_default_only' => 'Tik ne numatytieji',

    // Sėkmės pranešimai
    'activated_successfully'       => 'Miestas sėkmingai aktyvuotas',
    'deactivated_successfully'     => 'Miestas sėkmingai išjungtas',
    'set_as_capital_success'       => 'Miestas sėkmingai nustatytas sostine',
    'removed_from_capital_success' => 'Sostinės statusas sėkmingai pašalintas',
    'set_as_default_success'       => 'Miestas sėkmingai nustatytas numatytuoju',
    'removed_from_default_success' => 'Numatytojo statusas sėkmingai pašalintas',

    // Masinių veiksmų sėkmė
    'bulk_activated_success'      => 'Pažymėti miestai sėkmingai aktyvuoti',
    'bulk_deactivated_success'    => 'Pažymėti miestai sėkmingai išjungti',
    'bulk_set_capital_success'    => 'Pažymėti miestai sėkmingai tapo sostinėmis',
    'bulk_remove_capital_success' => 'Pažymėtiems miestams sostinės statusas pašalintas',

    // Laiko žymos
    'created_at' => 'Sukurta',
    'updated_at' => 'Atnaujinta',

    // Navigacija
    'navigation_label' => 'Miestai',
    'navigation_group' => 'Vietos',
];
