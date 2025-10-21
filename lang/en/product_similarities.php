<?php

declare(strict_types=1);

return [
    'title'  => 'Product Similarities',
    'plural' => 'Product Similarities',
    'single' => 'Product Similarity',

    'product'          => 'Primary Product',
    'similar_product'  => 'Similar Product',
    'algorithm_type'   => 'Algorithm Type',
    'similarity_score' => 'Similarity Score',
    'similarity_data'  => 'Calculation Data',

    'filters' => [
        'product'         => 'Product',
        'similar_product' => 'Similar Product',
        'algorithm_type'  => 'Algorithm Type',
        'min_score'       => 'Minimum Score',
        'max_score'       => 'Maximum Score',
    ],

    'algorithms' => [
        'cosine_similarity'  => 'Cosine Similarity',
        'jaccard_similarity' => 'Jaccard Similarity',
    ],
];
