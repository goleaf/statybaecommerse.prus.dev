<?php

declare(strict_types=1);

return [
    'title'  => 'Produktų panašumai',
    'plural' => 'Produktų panašumai',
    'single' => 'Produktų panašumas',
    'navigation_label'    => 'Produktų panašumai',
    'model_label'         => 'Produktų panašumas',
    'plural_model_label'  => 'Produktų panašumai',

    'sections' => [
        'products'            => 'Produktai',
        'similarity_details'  => 'Panašumo informacija',
        'metadata'            => 'Metaduomenys',
    ],

    'fields' => [
        'product'            => 'Produktas',
        'similar_product'    => 'Panašus produktas',
        'algorithm_type'     => 'Algoritmo tipas',
        'similarity_score'   => 'Panašumo įvertis',
        'calculation_data'   => 'Skaičiavimo duomenys',
        'data_point_key'     => 'Raktas',
        'data_point_value'   => 'Reikšmė',
        'calculated_at'      => 'Apskaičiuota',
        'created_at'         => 'Sukurta',
        'updated_at'         => 'Atnaujinta',
        'min_score'          => 'Minimalus įvertis',
        'max_score'          => 'Maksimalus įvertis',
        'calculated_from'    => 'Apskaičiuota nuo',
        'calculated_until'   => 'Apskaičiuota iki',
    ],

    'actions' => [
        'add_data_point' => 'Pridėti duomenų tašką',
    ],

    'algorithm_types' => [
        'cosine_similarity'   => 'Kosinusinis panašumas',
        'jaccard_similarity'  => 'Jaccard panašumas',
        'pearson_correlation' => 'Pearson koreliacija',
    ],
];
