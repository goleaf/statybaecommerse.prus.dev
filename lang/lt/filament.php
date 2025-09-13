<?php

return [
    'navigation' => [
        'content_management' => 'Turinio valdymas',
        'news' => 'Naujienos',
    ],

    'news' => [
        'model_label' => 'Naujiena',
        'plural_model_label' => 'Naujienos',

        'sections' => [
            'basic_information' => 'Pagrindinė informacija',
            'translations' => 'Vertimai',
            'categories_and_tags' => 'Kategorijos ir žymės',
            'images' => 'Paveikslėliai',
            'meta_data' => 'Meta duomenys',
        ],

        'fields' => [
            'author_name' => 'Autoriaus vardas',
            'author_email' => 'Autoriaus el. paštas',
            'is_visible' => 'Matoma',
            'is_featured' => 'Išskirtinė',
            'published_at' => 'Paskelbimo data',
            'translations' => 'Vertimai',
            'locale' => 'Kalba',
            'title' => 'Pavadinimas',
            'slug' => 'URL adresas',
            'summary' => 'Santrauka',
            'content' => 'Turinys',
            'seo_title' => 'SEO pavadinimas',
            'seo_description' => 'SEO aprašymas',
            'categories' => 'Kategorijos',
            'tags' => 'Žymės',
            'images' => 'Paveikslėliai',
            'image_file' => 'Paveikslėlio failas',
            'alt_text' => 'Alternatyvus tekstas',
            'caption' => 'Antraštė',
            'is_featured_image' => 'Pagrindinis paveikslėlis',
            'sort_order' => 'Rūšiavimo tvarka',
            'meta_data' => 'Meta duomenys',
            'meta_key' => 'Raktas',
            'meta_value' => 'Reikšmė',
            'featured_image' => 'Pagrindinis paveikslėlis',
            'view_count' => 'Peržiūrų skaičius',
            'created_at' => 'Sukurta',
        ],

        'filters' => [
            'published_at' => 'Paskelbimo data',
            'published_from' => 'Paskelbta nuo',
            'published_until' => 'Paskelbta iki',
            'view_count' => 'Peržiūrų skaičius',
            'view_count_from' => 'Peržiūrų skaičius nuo',
            'view_count_to' => 'Peržiūrų skaičius iki',
        ],

        'actions' => [
            'duplicate' => 'Kopijuoti',
            'mark_visible' => 'Pažymėti kaip matomą',
            'mark_hidden' => 'Pažymėti kaip paslėptą',
            'mark_featured' => 'Pažymėti kaip išskirtinę',
            'unmark_featured' => 'Pašalinti išskirtinio žymėjimą',
            'delete' => 'Ištrinti',
        ],
    ],
];
