<?php

declare(strict_types=1);

return [
    'title'  => 'Produktų panašumai',
    'plural' => 'Produktų panašumai',
    'single' => 'Produktų panašumas',
    'navigation_label'    => 'Produktų panašumai',
    'model_label'         => 'Produktų panašumas',
    'plural_model_label'  => 'Produktų panašumai',

    'product'          => 'Pagrindinis produktas',
    'similar_product'  => 'Panašus produktas',
    'algorithm_type'   => 'Algoritmo tipas',
    'similarity_score' => 'Panašumo balas',
    'similarity_data'  => 'Skaičiavimo duomenys',

    'filters' => [
        'product'         => 'Produktas',
        'similar_product' => 'Panašus produktas',
        'algorithm_type'  => 'Algoritmo tipas',
        'min_score'       => 'Mažiausias balas',
        'max_score'       => 'Didžiausias balas',
    ],

    'algorithms' => [
        'cosine_similarity'  => 'Kosinis panašumas',
        'jaccard_similarity' => 'Jaccard panašumas',
    ],
];
