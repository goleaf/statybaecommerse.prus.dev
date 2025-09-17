<?php

return [
    'navigation' => [
        'label' => 'Prekės ženklai',
    ],

    'model' => [
        'singular' => 'Prekės ženklas',
        'plural' => 'Prekės ženklai',
    ],

    'tabs' => [
        'basic_information' => 'Pagrindinė informacija',
        'seo' => 'SEO',
        'translations' => 'Vertimai',
        'with_products' => 'Su produktais',
        'without_products' => 'Be produktų',
    ],

    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'media' => 'Medija',
        'seo' => 'SEO nustatymai',
        'translations' => 'Vertimai',
        'description' => 'Aprašymas',
        'statistics' => 'Statistika',
        'timestamps' => 'Laiko žymos',
    ],

    'fields' => [
        'name' => 'Pavadinimas',
        'slug' => 'URL adresas',
        'description' => 'Aprašymas',
        'website' => 'Svetainė',
        'is_enabled' => 'Aktyvus',
        'logo' => 'Logotipas',
        'banner' => 'Baneris',
        'seo_title' => 'SEO pavadinimas',
        'seo_description' => 'SEO aprašymas',
        'translations' => 'Vertimai',
        'locale' => 'Kalbos kodas',
        'products_count' => 'Produktų skaičius',
        'translations_count' => 'Vertimų skaičius',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    'helpers' => [
        'seo_title' => 'Rekomenduojamas ilgis: 50-60 simbolių',
        'seo_description' => 'Rekomenduojamas ilgis: 150-160 simbolių',
        'enabled' => 'Aktyvūs prekės ženklai bus rodomi svetainėje',
    ],

    'placeholders' => [
        'no_website' => 'Svetainės nėra',
        'no_description' => 'Aprašymo nėra',
    ],

    'filters' => [
        'enabled' => 'Aktyvumo būsena',
        'all_brands' => 'Visi prekės ženklai',
        'enabled_only' => 'Tik aktyvūs',
        'disabled_only' => 'Tik neaktyvūs',
        'has_products' => 'Turi produktų',
        'has_website' => 'Turi svetainės',
        'has_logo' => 'Turi logotipo',
        'has_banner' => 'Turi banerio',
        'has_translations' => 'Turi vertimų',
        'translation_locale' => 'Vertimo kalba',
        'created_from' => 'Sukurta nuo',
        'created_until' => 'Sukurta iki',
    ],

    'actions' => [
        'create' => 'Sukurti prekės ženklą',
        'create_first_brand' => 'Sukurti pirmą prekės ženklą',
        'add_translation' => 'Pridėti vertimą',
        'enable_selected' => 'Aktyvuoti pasirinktus',
        'disable_selected' => 'Deaktyvuoti pasirinktus',
        'enable' => 'Aktyvuoti',
        'disable' => 'Deaktyvuoti',
        'manage_translations' => 'Tvarkyti vertimus',
        'bulk_actions' => 'Masiniai veiksmai',
    ],

    'messages' => [
        'slug_copied' => 'URL adresas nukopijuotas į iškarpinę',
    ],

    'notifications' => [
        'created' => 'Prekės ženklas sėkmingai sukurtas',
        'updated' => 'Prekės ženklas sėkmingai atnaujintas',
        'deleted' => 'Prekės ženklas sėkmingai ištrintas',
    ],

    'empty_state' => [
        'heading' => 'Prekės ženklų nerasta',
        'description' => 'Pradėkite kurdami savo pirmą prekės ženklą.',
    ],

    'stats' => [
        'total_brands' => 'Iš viso prekės ženklų',
        'total_brands_description' => 'Bendras prekės ženklų skaičius sistemoje',
        'enabled_brands' => 'Aktyvūs prekės ženklai',
        'enabled_brands_description' => 'Prekės ženklai, kurie rodomi svetainėje',
        'brands_with_products' => 'Su produktais',
        'brands_with_products_description' => 'Prekės ženklai, turintys produktų',
        'brands_with_translations' => 'Su vertimais',
        'brands_with_translations_description' => 'Prekės ženklai su vertimais',
    ],

    'widgets' => [
        'overview_heading' => 'Naujausi prekės ženklai',
        'performance_heading' => 'Prekės ženklų veikla',
        'products_count' => 'Produktų skaičius',
        'translations_count' => 'Vertimų skaičius',
    ],
];
