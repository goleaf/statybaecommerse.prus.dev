<?php

return [
    // Navigation
    'navigation' => [
        'label' => 'Šalys',
    ],

    // Model labels
    'model' => [
        'singular' => 'Šalis',
        'plural' => 'Šalys',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'geographic_information' => 'Geografinė informacija',
        'currency_economic' => 'Valiutos ir ekonominė informacija',
        'contact_information' => 'Kontaktinė informacija',
        'additional_information' => 'Papildoma informacija',
        'status_settings' => 'Būsena ir nustatymai',
    ],

    // Fields
    'fields' => [
        'name' => 'Pavadinimas',
        'name_official' => 'Oficialus pavadinimas',
        'cca2' => 'CCA2 kodas',
        'cca3' => 'CCA3 kodas',
        'ccn3' => 'CCN3 kodas',
        'region' => 'Regionas',
        'subregion' => 'Subregionas',
        'latitude' => 'Platuma',
        'longitude' => 'Ilguma',
        'currency_code' => 'Valiutos kodas',
        'currency_symbol' => 'Valiutos simbolis',
        'vat_rate' => 'PVM tarifas',
        'timezone' => 'Laiko juosta',
        'phone_code' => 'Telefono kodas',
        'phone_calling_code' => 'Skambinimo kodas',
        'flag' => 'Vėliava',
        'svg_flag' => 'SVG vėliava',
        'currencies' => 'Valiutos',
        'languages' => 'Kalbos',
        'timezones' => 'Laiko juostos',
        'metadata' => 'Metaduomenys',
        'description' => 'Aprašymas',
        'is_active' => 'Aktyvus',
        'is_enabled' => 'Įjungtas',
        'is_eu_member' => 'ES narė',
        'requires_vat' => 'Reikalauja PVM',
        'sort_order' => 'Rūšiavimo tvarka',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    // Helpers
    'helpers' => [
        'active' => 'Ar ši šalis yra aktyvi ir matoma',
        'enabled' => 'Ar ši šalis įjungta naudojimui',
        'eu_member' => 'Ar ši šalis yra Europos Sąjungos narė',
        'requires_vat' => 'Ar ši šalis reikalauja PVM sandoriams',
        'vat_rate' => 'PVM tarifas procentais (0-100)',
        'sort_order' => 'Šalių rodymo tvarka (mažesni skaičiai rodomi pirmi)',
    ],

    // Placeholders
    'placeholders' => [
        'no_flag' => 'Nėra vėliavos',
        'no_description' => 'Nėra aprašymo',
    ],

    // Actions
    'actions' => [
        'activate' => 'Aktyvuoti',
        'deactivate' => 'Deaktyvuoti',
        'activate_selected' => 'Aktyvuoti pasirinktus',
        'deactivate_selected' => 'Deaktyvuoti pasirinktus',
        'activated_successfully' => 'Šalys sėkmingai aktyvuotos',
        'deactivated_successfully' => 'Šalys sėkmingai deaktyvuotos',
    ],

    // Filters
    'filters' => [
        'active' => 'Aktyvumo būsena',
        'enabled' => 'Įjungimo būsena',
        'eu_member' => 'ES narystės būsena',
        'requires_vat' => 'PVM reikalavimo būsena',
        'region' => 'Regionas',
        'currency_code' => 'Valiutos kodas',
    ],

    // Statistics
    'stats' => [
        'total_countries' => 'Iš viso šalių',
        'active_countries' => 'Aktyvių šalių',
        'eu_members' => 'ES narių',
        'countries_with_vat' => 'Šalių su PVM',
    ],
];
