<?php

declare(strict_types=1);

return [
    'title'  => 'Product Similarities',
    'plural' => 'Product Similarities',
    'single' => 'Product Similarity',
    'navigation_label'    => 'Product Similarities',
    'model_label'         => 'Product Similarity',
    'plural_model_label'  => 'Product Similarities',

    'basic_information' => 'Similarity Details',

    'product'          => 'Product',
    'similar_product'  => 'Similar Product',
    'algorithm_type'   => 'Algorithm Type',
    'similarity_score' => 'Similarity Score',
    'calculation_data' => 'Calculation Data',
    'calculation_data_help' => 'Optional metadata describing how the similarity score was calculated.',
    'calculated_at'    => 'Calculated At',
    'created_at'       => 'Created At',
    'updated_at'       => 'Updated At',

    'filters' => [
        'product'         => 'Product',
        'similar_product' => 'Similar Product',
        'algorithm_type'  => 'Algorithm Type',
        'min_score'       => 'Minimum Score',
        'max_score'       => 'Maximum Score',
    ],

    'algorithm_types' => [
        'cosine'  => 'Cosine Similarity',
        'jaccard' => 'Jaccard Similarity',
    ],
];
