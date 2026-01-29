<?php

declare(strict_types=1);

return [
    'link_search' => [
        'placeholder' => 'Produkte, Kategorien oder Kollektionen suchen oder eine URL einfügen',
        'static'      => [
            'collections' => [
                'description' => 'Kuratierten Produktkollektionen.',
                'label'       => 'Kollektionen',
            ],
            'contact' => [
                'description' => 'Kontaktmöglichkeiten für Kund:innen.',
                'label'       => 'Kontaktseite',
            ],
            'home' => [
                'description' => 'Zentrale Einstiegsseite des Shops.',
                'label'       => 'Startseite',
            ],
            'posts' => [
                'description' => 'Aktuelle Artikel unseres Teams.',
                'label'       => 'Blog',
            ],
            'products' => [
                'description' => 'Entdecken Sie das gesamte Sortiment.',
                'label'       => 'Alle Produkte',
            ],
        ],
        'types' => [
            'category'   => 'Kategorie',
            'collection' => 'Kollektion',
            'post'       => 'Blogbeitrag',
            'product'    => 'Produkt',
            'static'     => 'Statische Seite',
        ],
    ],
];
