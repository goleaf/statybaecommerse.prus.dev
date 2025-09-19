<?php

return [
    // Basic fields
    'name' => 'Pavadinimas',
    'slug' => 'Nuoroda',
    'description' => 'Aprašymas',
    'is_visible' => 'Matomas',
    'is_automatic' => 'Automatinis',
    'sort_order' => 'Rūšiavimo tvarka',
    'seo_title' => 'SEO pavadinimas',
    'seo_description' => 'SEO aprašymas',
    'is_active' => 'Aktyvus',
    'rules' => 'Taisyklės',
    'max_products' => 'Maksimalus produktų skaičius',
    'meta_title' => 'Meta pavadinimas',
    'meta_description' => 'Meta aprašymas',
    'meta_keywords' => 'Meta raktažodžiai',
    'display_type' => 'Rodymo tipas',
    'products_per_page' => 'Produktų per puslapį',
    'show_filters' => 'Rodyti filtrus',
    'image' => 'Paveikslėlis',
    'banner' => 'Baneris',

    // Helpers
    'full_display_name' => 'Pilnas rodymo pavadinimas',
    'seo_info' => 'SEO informacija',
    'business_info' => 'Verslo informacija',
    'complete_info' => 'Pilna informacija',
    'products_count' => 'Produktų skaičius',
    'type' => 'Tipas',
    'automatic' => 'Automatinis',
    'manual' => 'Rankinis',

    // Filters
    'filters' => [
        'is_visible' => 'Matomumas',
        'is_automatic' => 'Tipas',
        'has_products' => 'Turi produktus',
        'created_from' => 'Sukurta nuo',
        'created_until' => 'Sukurta iki',
        'display_type' => 'Rodymo tipas',
        'show_filters' => 'Rodyti filtrus',
    ],

    // Actions
    'actions' => [
        'view' => 'Peržiūrėti',
        'edit' => 'Redaguoti',
        'delete' => 'Ištrinti',
        'toggle_visibility' => 'Perjungti matomumą',
        'manage_products' => 'Tvarkyti produktus',
    ],

    // Confirmations
    'confirmations' => [
        'toggle_visibility' => 'Ar tikrai norite perjungti šios kolekcijos matomumą?',
        'delete' => 'Ar tikrai norite ištrinti šią kolekciją? Šis veiksmas negali būti atšauktas.',
    ],

    // Empty states
    'empty_states' => [
        'no_collections' => 'Kolekcijų nerasta',
        'no_products' => 'Šioje kolekcijoje nėra produktų',
        'no_translations' => 'Vertimų nėra',
    ],

    // Messages
    'messages' => [
        'created' => 'Kolekcija sėkmingai sukurta',
        'updated' => 'Kolekcija sėkmingai atnaujinta',
        'deleted' => 'Kolekcija sėkmingai ištrinta',
        'products_managed' => 'Produktai sėkmingai sutvarkyti',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Pavadinimas yra privalomas',
        'slug_required' => 'Nuoroda yra privaloma',
        'slug_unique' => 'Nuoroda turi būti unikali',
        'description_max' => 'Aprašymas negali viršyti 1000 simbolių',
        'max_products_numeric' => 'Maksimalus produktų skaičius turi būti skaičius',
        'sort_order_numeric' => 'Rūšiavimo tvarka turi būti skaičius',
    ],

    // Statistics
    'stats' => [
        'total_collections' => 'Iš viso kolekcijų',
        'visible_collections' => 'Matomų kolekcijų',
        'automatic_collections' => 'Automatinių kolekcijų',
        'manual_collections' => 'Rankinių kolekcijų',
        'collections_with_products' => 'Kolekcijų su produktais',
        'avg_products_per_collection' => 'Vidutinis produktų skaičius kolekcijoje',
        'all_collections' => 'Visos sistemos kolekcijos',
        'visible_to_customers' => 'Matomos klientams',
        'auto_generated' => 'Automatiškai sugeneruotos kolekcijos',
        'manually_created' => 'Rankiniu būdu sukurtos kolekcijos',
        'average_products' => 'Vidutinis produktų skaičius',
    ],

    // Widgets
    'widgets' => [
        'stats_heading' => 'Kolekcijų statistika',
        'performance_heading' => 'Kolekcijų veikimas',
        'charts' => [
            'products_count' => 'Produktų skaičius',
        ],
    ],

    // Display types
    'display_types' => [
        'grid' => 'Tinklelis',
        'list' => 'Sąrašas',
        'carousel' => 'Karuselė',
    ],

    // Status
    'status' => [
        'visible' => 'Matomas',
        'hidden' => 'Paslėptas',
    ],

    // Types
    'types' => [
        'automatic' => 'Automatinis',
        'manual' => 'Rankinis',
    ],

    // Placeholders
    'placeholders' => [
        'name' => 'Įveskite kolekcijos pavadinimą',
        'slug' => 'Įveskite kolekcijos nuorodą',
        'description' => 'Įveskite kolekcijos aprašymą',
        'seo_title' => 'Įveskite SEO pavadinimą',
        'seo_description' => 'Įveskite SEO aprašymą',
        'meta_title' => 'Įveskite meta pavadinimą',
        'meta_description' => 'Įveskite meta aprašymą',
        'meta_keywords' => 'Įveskite meta raktažodžius (atskirtus kableliais)',
    ],

    // Help text
    'help' => [
        'slug' => 'URL draugiška pavadinimo versija',
        'is_visible' => 'Ar kolekcija matoma klientams',
        'is_automatic' => 'Ar kolekcija automatiškai generuojama',
        'sort_order' => 'Tvarka, kuria kolekcijos rodomos',
        'max_products' => 'Maksimalus produktų skaičius šioje kolekcijoje',
        'rules' => 'Taisyklės automatinio kolekcijos generavimo',
        'display_type' => 'Kaip produktai rodomi šioje kolekcijoje',
        'products_per_page' => 'Produktų skaičius per puslapį',
        'show_filters' => 'Ar rodyti produktų filtrus',
        'meta_keywords' => 'Kableliais atskirti raktažodžiai SEO',
    ],
];