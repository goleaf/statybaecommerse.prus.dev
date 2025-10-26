<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Skaidrių valdymas',
    'plural'           => 'Skaidrės',
    'single'           => 'Skaidrė',

    'basic_information' => 'Pagrindinė informacija',
    'media'             => 'Medija',
    'appearance'        => 'Išvaizda',
    'settings'          => 'Nustatymai',

    'title'                  => 'Pavadinimas',
    'description'            => 'Aprašymas',
    'button_text'            => 'Mygtuko tekstas',
    'button_url'             => 'Mygtuko URL',
    'button_url_placeholder' => 'Ieškokite turinio arba įklijuokite URL',
    'button_url_helper'      => 'Pasirinkite vidinį puslapį, produktą arba įveskite išorinį URL.',
    'image'                  => 'Paveikslėlis',
    'background_color'       => 'Fono spalva',
    'text_color'             => 'Teksto spalva',
    'sort_order'             => 'Rūšiavimo tvarka',
    'is_active'              => 'Aktyvus',
    'created_at'             => 'Sukurta',
    'updated_at'             => 'Atnaujinta',

    'link_search' => [
        'placeholder' => 'Ieškokite produktų, kategorijų, kolekcijų arba įklijuokite nuorodą',
        'types'       => [
            'static'     => 'Statinis puslapis',
            'product'    => 'Produktas',
            'category'   => 'Kategorija',
            'collection' => 'Kolekcija',
            'post'       => 'Tinklaraščio įrašas',
        ],
        'static_links' => [
            'home' => [
                'route'       => 'home',
                'label'       => 'Pagrindinis puslapis',
                'description' => 'Pagrindinis parduotuvės puslapis.',
            ],
            'products' => [
                'route'       => 'frontend.products.index',
                'label'       => 'Visi produktai',
                'description' => 'Peržiūrėkite visą katalogą.',
            ],
            'collections' => [
                'route'       => 'frontend.collections.index',
                'label'       => 'Kolekcijų apžvalga',
                'description' => 'Kruopščiai atrinktos produktų kolekcijos.',
            ],
            'posts' => [
                'route'       => 'frontend.posts.index',
                'label'       => 'Tinklaraštis',
                'description' => 'Naujausi mūsų komandos straipsniai.',
            ],
            'contact' => [
                'route'       => 'frontend.contact.index',
                'label'       => 'Kontaktai',
                'description' => 'Klientų aptarnavimo kontaktai.',
            ],
        ],
    ],
];
