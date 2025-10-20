<?php

declare(strict_types=1);

return [
    'title'  => 'Product Similarities',
    'plural' => 'Product Similarities',
    'single' => 'Product Similarity',

    'sections' => [
        'products'            => 'Products',
        'similarity_details'  => 'Similarity Details',
        'metadata'            => 'Metadata',
    ],

    'fields' => [
        'product'            => 'Product',
        'similar_product'    => 'Similar Product',
        'algorithm_type'     => 'Algorithm Type',
        'similarity_score'   => 'Similarity Score',
        'calculation_data'   => 'Calculation Data',
        'data_point_key'     => 'Key',
        'data_point_value'   => 'Value',
        'calculated_at'      => 'Calculated At',
        'created_at'         => 'Created At',
        'updated_at'         => 'Updated At',
        'min_score'          => 'Minimum Score',
        'max_score'          => 'Maximum Score',
        'calculated_from'    => 'Calculated From',
        'calculated_until'   => 'Calculated Until',
    ],

    'actions' => [
        'add_data_point' => 'Add data point',
    ],

    'algorithm_types' => [
        'cosine_similarity'   => 'Cosine Similarity',
        'jaccard_similarity'  => 'Jaccard Similarity',
        'pearson_correlation' => 'Pearson Correlation',
    ],
];
