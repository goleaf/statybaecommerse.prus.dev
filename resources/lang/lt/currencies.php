<?php

declare(strict_types=1);

return [
    'plural' => 'Valiutos',
    'single' => 'Valiuta',

    'basic_information' => 'Pagrindinė informacija',
    'name'              => 'Pavadinimas',
    'code'              => 'Valiutos kodas',
    'code_help'         => 'Naudokite trijų raidžių ISO valiutos kodą, pvz., EUR ar USD.',
    'symbol'            => 'Simbolis',
    'symbol_help'       => 'Simbolis, rodomas šalia kainų, pavyzdžiui €, $ ar £.',
    'iso_code'          => 'ISO identifikatorius',
    'iso_code_help'     => 'Neprivalomas išplėstas ISO ar banko identifikatorius vidinei apskaitai.',
    'description'       => 'Aprašymas',

    'exchange_rates'     => 'Valiutų kursai',
    'exchange_rate'      => 'Keitimo kursas',
    'exchange_rate_help' => 'Nurodykite keitimo kursą, lyginant su pasirinkta bazine valiuta.',
    'base_currency'      => 'Bazinė valiuta',
    'base_currency_help' => 'Valiuta, naudojama kaip konversijų atskaitos taškas.',

    'formatting'      => 'Formatavimas',
    'decimal_places'  => 'Skaitmenys po kablelio',
    'symbol_position' => 'Simbolio pozicija',
    'positions'       => [
        'before' => 'Prieš sumą',
        'after'  => 'Po sumos',
    ],
    'thousands_separator'      => 'Tūkstančių skirtukas',
    'thousands_separator_help' => 'Simbolis, skiriantis tūkstančius, pvz., kablelis ar tarpas.',
    'decimal_separator'        => 'Dešimtainių skirtukas',
    'decimal_separator_help'   => 'Simbolis, skiriantis dešimtaines, pvz., taškas ar kablelis.',

    'settings'         => 'Nustatymai',
    'is_active'        => 'Aktyvi',
    'is_default'       => 'Numatytoji valiuta',
    'sort_order'       => 'Rikiavimo tvarka',
    'auto_update_rate' => 'Automatinis kurso atnaujinimas',

    'created_at' => 'Sukurta',
    'updated_at' => 'Atnaujinta',

    'active_only'        => 'Tik aktyvios',
    'inactive_only'      => 'Tik neaktyvios',
    'default_only'       => 'Tik numatytosios',
    'non_default_only'   => 'Tik nenumatytosios',
    'auto_update_only'   => 'Tik automatiniai atnaujinimai',
    'manual_update_only' => 'Tik rankiniai atnaujinimai',

    'deactivate'                  => 'Išjungti',
    'activate'                    => 'Įjungti',
    'activated_successfully'      => 'Valiuta sėkmingai įjungta.',
    'deactivated_successfully'    => 'Valiuta sėkmingai išjungta.',
    'set_default'                 => 'Nustatyti kaip numatytąją',
    'set_as_default_successfully' => 'Valiuta sėkmingai nustatyta kaip numatytoji.',
    'update_rate'                 => 'Atnaujinti kursą',
    'rate_updated_successfully'   => 'Valiutos kursas sėkmingai atnaujintas.',
    'rate_update_failed'          => 'Nepavyko atnaujinti valiutos kurso.',

    'activate_selected'          => 'Įjungti pažymėtas',
    'deactivate_selected'        => 'Išjungti pažymėtas',
    'bulk_activated_success'     => 'Pasirinktos valiutos sėkmingai įjungtos.',
    'bulk_deactivated_success'   => 'Pasirinktos valiutos sėkmingai išjungtos.',
    'update_rates'               => 'Atnaujinti kursus',
    'rates_updated_successfully' => 'Keitimo kursai sėkmingai atnaujinti.',
    'rates_update_failed'        => 'Nepavyko atnaujinti jokių valiutų kursų. Patikrinkite tiekėjo nustatymus.',
];
