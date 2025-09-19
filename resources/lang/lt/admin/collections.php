<?php

return [
    
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'seo_settings' => 'SEO nustatymai',
        'media' => 'Medija',
    ],
    
    'fields' => [
        'name' => 'Pavadinimas',
        'slug' => 'URL slug',
        'description' => 'Aprašymas',
        'is_visible' => 'Matoma klientams',
        'is_automatic' => 'Automatinė kolekcija',
        'sort_order' => 'Rūšiavimo tvarka',
        'max_products' => 'Maksimalus produktų skaičius',
        'rules' => 'Taisyklės',
        'rule_key' => 'Taisyklės raktas',
        'rule_value' => 'Taisyklės reikšmė',
        'seo_title' => 'SEO pavadinimas',
        'seo_description' => 'SEO aprašymas',
        'meta_title' => 'Meta pavadinimas',
        'meta_description' => 'Meta aprašymas',
        'meta_keywords' => 'Meta raktažodžiai',
        'display_type' => 'Rodymo tipas',
        'products_per_page' => 'Produktų per puslapį',
        'show_filters' => 'Rodyti filtrus',
        'image' => 'Paveikslėlis',
        'banner' => 'Baneris',
    ],
    
    'placeholders' => [
        'name' => 'Įveskite kolekcijos pavadinimą',
        'slug' => 'kolekcijos-url',
        'description' => 'Įveskite kolekcijos aprašymą',
        'seo_title' => 'SEO pavadinimas',
        'seo_description' => 'SEO aprašymas',
        'meta_title' => 'Meta pavadinimas',
        'meta_description' => 'Meta aprašymas',
        'meta_keywords' => 'raktažodis1, raktažodis2, raktažodis3',
    ],
    
    'help' => [
        'slug' => 'URL dalis, kuri bus naudojama kolekcijos puslapyje',
        'is_visible' => 'Ar kolekcija bus matoma klientams',
        'is_automatic' => 'Ar kolekcija generuojama automatiškai pagal taisykles',
        'sort_order' => 'Skaičius, pagal kurį rūšiuojamos kolekcijos',
        'max_products' => 'Maksimalus produktų skaičius kolekcijoje (0 = neribotai)',
        'rules' => 'Taisyklės automatiniams kolekcijų generavimui',
        'meta_keywords' => 'Raktažodžiai atskirti kableliais',
        'display_type' => 'Kaip rodyti produktus kolekcijoje',
        'products_per_page' => 'Kiek produktų rodyti viename puslapyje',
        'show_filters' => 'Ar rodyti filtrus kolekcijos puslapyje',
    ],
    
    'display_types' => [
        'grid' => 'Tinklelis',
        'list' => 'Sąrašas',
        'carousel' => 'Karuselė',
    ],
    
    'table' => [
        'name' => 'Pavadinimas',
        'slug' => 'URL slug',
        'description' => 'Aprašymas',
        'is_visible' => 'Matoma',
        'is_automatic' => 'Automatinė',
        'products_count' => 'Produktų sk.',
        'sort_order' => 'Rūšiavimas',
        'display_type' => 'Rodymo tipas',
        'products_per_page' => 'Per puslapį',
        'show_filters' => 'Filtrai',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
        'image' => 'Paveikslėlis',
    ],
    
    'filters' => [
        'is_visible' => 'Matomumas',
        'is_automatic' => 'Tipas',
        'has_products' => 'Turi produktų',
        'created_from' => 'Sukurta nuo',
        'created_until' => 'Sukurta iki',
        'display_type' => 'Rodymo tipas',
        'show_filters' => 'Rodo filtrus',
    ],
    
    'status' => [
        'visible' => 'Matoma',
        'hidden' => 'Paslėpta',
    ],
    
    'types' => [
        'manual' => 'Rankinė',
        'automatic' => 'Automatinė',
    ],
    
    'actions' => [
        'view' => 'Peržiūrėti',
        'edit' => 'Redaguoti',
        'delete' => 'Ištrinti',
        'toggle_visibility' => 'Perjungti matomumą',
        'manage_products' => 'Valdyti produktus',
    ],
    
    'confirmations' => [
        'toggle_visibility' => 'Ar tikrai norite pakeisti kolekcijos matomumą?',
        'delete' => 'Ar tikrai norite ištrinti šią kolekciją?',
    ],
    
    'stats' => [
        'total_collections' => 'Iš viso kolekcijų',
        'all_collections' => 'Visos kolekcijos',
        'visible_collections' => 'Matomos kolekcijos',
        'visible_to_customers' => 'Matomos klientams',
        'automatic_collections' => 'Automatinės kolekcijos',
        'auto_generated' => 'Automatiškai generuojamos',
        'manual_collections' => 'Rankinės kolekcijos',
        'manually_created' => 'Rankiniu būdu sukurtos',
        'have_products' => 'Turi produktų',
        'avg_products_per_collection' => 'Vid. produktų per kolekciją',
        'average_products' => 'Vidutinis produktų skaičius',
    ],
    
    'charts' => [
        'performance_heading' => 'Kolekcijų našumas',
        'products_count' => 'Produktų skaičius',
    ],
    
    'widgets' => [
    ],
];
