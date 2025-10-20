<?php

declare(strict_types=1);

return [
    'title'  => 'Produktähnlichkeiten',
    'plural' => 'Produktähnlichkeiten',
    'single' => 'Produktähnlichkeit',

    'product'          => 'Hauptprodukt',
    'similar_product'  => 'Ähnliches Produkt',
    'algorithm_type'   => 'Algorithmustyp',
    'similarity_score' => 'Ähnlichkeitswert',
    'similarity_data'  => 'Berechnungsdaten',

    'filters' => [
        'product'         => 'Produkt',
        'similar_product' => 'Ähnliches Produkt',
        'algorithm_type'  => 'Algorithmustyp',
        'min_score'       => 'Mindestwert',
        'max_score'       => 'Höchstwert',
    ],

    'algorithms' => [
        'cosine_similarity'  => 'Kosinus-Ähnlichkeit',
        'jaccard_similarity' => 'Jaccard-Ähnlichkeit',
    ],
];
