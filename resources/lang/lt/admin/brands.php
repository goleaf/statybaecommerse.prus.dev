<?php

return [
    // Navigation
    'navigation' => [
        'label' => 'Prekių ženklai',
    ],

    // Model labels
    'model' => [
        'singular' => 'Prekių ženklas',
        'plural' => 'Prekių ženklai',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'seo' => 'SEO nustatymai',
        'translations' => 'Vertimai',
    ],

    // Fields
    'fields' => [
        'name' => 'Pavadinimas',
        'slug' => 'Slug',
        'description' => 'Aprašymas',
        'website' => 'Svetainė',
        'is_enabled' => 'Įjungtas',
        'seo_title' => 'SEO pavadinimas',
        'seo_description' => 'SEO aprašymas',
        'translations' => 'Vertimai',
        'locale' => 'Kalba',
        'translations_count' => 'Vertimai',
        'products_count' => 'Produktai',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    // Helpers
    'helpers' => [
        'enabled' => 'Ar šis prekių ženklas yra aktyvus ir matomas',
        'seo_title' => 'Rekomenduojamas ilgis: 50-60 simbolių',
        'seo_description' => 'Rekomenduojamas ilgis: 150-160 simbolių',
    ],

    // Placeholders
    'placeholders' => [
        'no_website' => 'Nėra svetainės',
    ],

    // Actions
    'actions' => [
        'add_translation' => 'Pridėti vertimą',
        'enable' => 'Įjungti',
        'disable' => 'Išjungti',
        'enable_selected' => 'Įjungti pasirinktus',
        'disable_selected' => 'Išjungti pasirinktus',
        'manage_translations' => 'Valdyti vertimus',
        'bulk_actions' => 'Masiniai veiksmai',
    ],

    // Filters
    'filters' => [
        'enabled_only' => 'Tik įjungti',
        'has_products' => 'Turi produktų',
        'has_translations' => 'Turi vertimų',
        'translation_locale' => 'Vertimo kalba',
    ],

    // Statistics
    'stats' => [
        'total_brands' => 'Iš viso prekių ženklų',
        'total_brands_description' => 'Visi sistemos prekių ženklai',
        'enabled_brands' => 'Įjungti prekių ženklai',
        'enabled_brands_description' => 'Aktyvūs ir matomi prekių ženklai',
        'brands_with_products' => 'Prekių ženklai su produktais',
        'brands_with_products_description' => 'Prekių ženklai, turintys produktų',
        'brands_with_translations' => 'Prekių ženklai su vertimais',
        'brands_with_translations_description' => 'Prekių ženklai su daugiakalbės palaikymu',
    ],

    // Widgets
    'widgets' => [
        'brand_overview' => 'Prekių ženklų apžvalga',
        'brand_performance' => 'Prekių ženklų veikla',
        'brand_analytics' => 'Prekių ženklų analitika',
    ],

    // Empty states
    'empty_states' => [
        'no_brands' => 'Prekių ženklų nerasta',
        'no_enabled_brands' => 'Nėra įjungtų prekių ženklų',
        'no_brands_with_products' => 'Nėra prekių ženklų su produktais',
    ],

    // Messages
    'messages' => [
        'created' => 'Prekių ženklas sėkmingai sukurtas',
        'updated' => 'Prekių ženklas sėkmingai atnaujintas',
        'deleted' => 'Prekių ženklas sėkmingai ištrintas',
        'enabled' => 'Prekių ženklas sėkmingai įjungtas',
        'disabled' => 'Prekių ženklas sėkmingai išjungtas',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Prekių ženklo pavadinimas yra privalomas',
        'name_max' => 'Prekių ženklo pavadinimas negali viršyti 255 simbolių',
        'slug_required' => 'Prekių ženklo slug yra privalomas',
        'slug_unique' => 'Prekių ženklo slug turi būti unikalus',
        'slug_alpha_dash' => 'Prekių ženklo slug gali turėti tik raides, skaičius, brūkšnelius ir pabraukimus',
        'description_max' => 'Prekių ženklo aprašymas negali viršyti 1000 simbolių',
        'website_url' => 'Svetainė turi būti galiojantis URL',
        'website_max' => 'Svetainė negali viršyti 255 simbolių',
        'seo_title_max' => 'SEO pavadinimas negali viršyti 60 simbolių',
        'seo_description_max' => 'SEO aprašymas negali viršyti 160 simbolių',
    ],
];
