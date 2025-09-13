<?php

return [
    // Basic fields
    'title' => 'Kategorijos',
    'single' => 'Kategorija',
    'plural' => 'Kategorijos',
    'fields' => [
        'name' => 'Pavadinimas',
        'slug' => 'Slug',
        'description' => 'Aprašymas',
        'short_description' => 'Trumpas aprašymas',
        'parent' => 'Tėvinė kategorija',
        'sort_order' => 'Rūšiavimo eiliškumas',
        'is_enabled' => 'Įjungta',
        'is_visible' => 'Matoma',
        'is_featured' => 'Išskirtinė',
        'show_in_menu' => 'Rodyti meniu',
        'product_limit' => 'Produktų limitas',
        'seo_title' => 'SEO pavadinimas',
        'seo_description' => 'SEO aprašymas',
        'seo_keywords' => 'SEO raktažodžiai',
        'image' => 'Vaizdas',
        'banner' => 'Baneris',
        'gallery' => 'Galerija',
        'children' => 'Subkategorijos',
        'children_count' => 'Subkategorijos',
        'products_count' => 'Produktai',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'settings' => 'Nustatymai',
        'media' => 'Medija',
        'hierarchy' => 'Kategorijų hierarchija',
    ],

    // Tabs
    'tabs' => [
        'translations' => 'Vertimai',
        'lithuanian' => 'Lietuvių',
        'english' => 'Anglų',
    ],

    // Filters
    'filters' => [
        'is_enabled' => 'Įjungta',
        'is_featured' => 'Išskirtinė',
        'is_visible' => 'Matoma',
        'show_in_menu' => 'Rodyti meniu',
        'parent' => 'Tėvinė kategorija',
        'has_children' => 'Turi subkategorijų',
        'with_children' => 'Su subkategorijomis',
        'without_children' => 'Be subkategorijų',
        'has_products' => 'Turi produktų',
        'with_products' => 'Su produktais',
        'without_products' => 'Be produktų',
        'products_count_range' => 'Produktų skaičiaus diapazonas',
        'no_products' => 'Be produktų',
        '1_to_10_products' => '1-10 produktų',
        '11_to_50_products' => '11-50 produktų',
        '51_to_100_products' => '51-100 produktų',
        '100_plus_products' => '100+ produktų',
        'created_from' => 'Sukurta nuo',
        'created_until' => 'Sukurta iki',
        'has_seo' => 'Turi SEO',
        'root_categories' => 'Pagrindinės kategorijos',
    ],

    // Actions
    'actions' => [
        'translate' => 'Versti',
        'view_products' => 'Žiūrėti produktus',
        'duplicate' => 'Dublikuoti',
        'enable_selected' => 'Įjungti pasirinktus',
        'disable_selected' => 'Išjungti pasirinktus',
        'feature_selected' => 'Išskirti pasirinktus',
    ],

    // Bulk Actions
    'bulk_actions' => [
        'enable_selected' => 'Įjungti pasirinktus',
        'disable_selected' => 'Išjungti pasirinktus',
        'feature_selected' => 'Išskirti pasirinktus',
    ],

    // Messages
    'messages' => [
        'created' => 'Kategorija sėkmingai sukurta',
        'updated' => 'Kategorija sėkmingai atnaujinta',
        'deleted' => 'Kategorija sėkmingai ištrinta',
        'status_changed' => 'Kategorijos būsena sėkmingai pakeista',
        'featured_toggled' => 'Kategorijos išskirtinio statuso perjungimas sėkmingas',
        'no_categories_found' => 'Kategorijų nerasta',
        'create_first_category' => 'Sukurkite savo pirmąją kategoriją, kad pradėtumėte',
    ],

    // Help
    'help' => [
        'create_first_category' => 'Sukurkite savo pirmąją kategoriją, kad organizuotumėte savo produktus',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Kategorijos pavadinimas yra privalomas',
        'name_max' => 'Kategorijos pavadinimas negali viršyti 255 simbolių',
        'slug_required' => 'Kategorijos slug yra privalomas',
        'slug_unique' => 'Kategorijos slug turi būti unikalus',
        'slug_alpha_dash' => 'Kategorijos slug gali turėti tik raides, skaičius, brūkšnelius ir pabraukimus',
        'description_max' => 'Kategorijos aprašymas negali viršyti 1000 simbolių',
        'short_description_max' => 'Kategorijos trumpas aprašymas negali viršyti 500 simbolių',
        'seo_title_max' => 'SEO pavadinimas negali viršyti 255 simbolių',
        'seo_description_max' => 'SEO aprašymas negali viršyti 500 simbolių',
        'seo_keywords_max' => 'SEO raktažodžiai negali viršyti 255 simbolių',
        'sort_order_numeric' => 'Rūšiavimo eiliškumas turi būti skaičius',
        'product_limit_numeric' => 'Produktų limitas turi būti skaičius',
    ],
];
