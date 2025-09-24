<?php

return [
    'title' => 'Simple Recommendation Configurations',
    'plural' => 'Simple Recommendation Configurations',
    'single' => 'Simple Recommendation Configuration',

    'basic_information' => 'Basic Information',
    'name' => 'Name',
    'code' => 'Code',
    'description' => 'Description',

    'algorithm_settings' => 'Algorithm Settings',
    'algorithm_type' => 'Algorithm Type',
    'min_score' => 'Minimum Score',
    'max_results' => 'Maximum Results',
    'decay_factor' => 'Decay Factor',

    'algorithm_types' => [
        'collaborative' => 'Collaborative Filtering',
        'content_based' => 'Content-Based Filtering',
        'hybrid' => 'Hybrid Recommendation',
        'popularity' => 'Popularity-Based',
        'trending' => 'Trending Products',
        'similarity' => 'Similarity-Based',
        'custom' => 'Custom Algorithm',
    ],

    'filtering' => 'Filtering',
    'products' => 'Products',
    'categories' => 'Categories',
    'exclude_out_of_stock' => 'Exclude Out of Stock',
    'exclude_inactive' => 'Exclude Inactive',

    'weighting' => 'Weighting',
    'price_weight' => 'Price Weight',
    'rating_weight' => 'Rating Weight',
    'popularity_weight' => 'Popularity Weight',
    'recency_weight' => 'Recency Weight',
    'category_weight' => 'Category Weight',
    'custom_weight' => 'Custom Weight',

    'settings' => 'Settings',
    'is_active' => 'Is Active',
    'is_default' => 'Is Default',
    'cache_duration' => 'Cache Duration (minutes)',
    'sort_order' => 'Sort Order',
    'notes' => 'Notes',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    'products_count' => 'Products Count',
    'categories_count' => 'Categories Count',

    'help_texts' => [
        'min_score_help' => 'Minimum similarity score for recommendations',
        'max_results_help' => 'Maximum number of recommendations to return',
        'decay_factor_help' => 'Time decay factor for recency weighting',
        'price_weight_help' => 'Weight for price similarity in recommendations',
        'rating_weight_help' => 'Weight for rating similarity in recommendations',
        'popularity_weight_help' => 'Weight for popularity in recommendations',
        'recency_weight_help' => 'Weight for recency in recommendations',
        'category_weight_help' => 'Weight for category similarity in recommendations',
        'custom_weight_help' => 'Weight for custom factors in recommendations',
        'cache_duration_help' => 'How long to cache recommendations in minutes',
    ],

    'filters' => [
        'active_only' => 'Active Only',
        'inactive_only' => 'Inactive Only',
        'default_only' => 'Default Only',
        'non_default_only' => 'Non-Default Only',
        'exclude_out_of_stock_only' => 'Exclude Out of Stock Only',
        'include_out_of_stock_only' => 'Include Out of Stock Only',
        'exclude_inactive_only' => 'Exclude Inactive Only',
        'include_inactive_only' => 'Include Inactive Only',
    ],

    'actions' => [
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'set_default' => 'Set as Default',
        'activated_successfully' => 'Activated successfully',
        'deactivated_successfully' => 'Deactivated successfully',
        'set_as_default_successfully' => 'Set as default successfully',
        'bulk_activated_success' => 'Selected items activated successfully',
        'bulk_deactivated_success' => 'Selected items deactivated successfully',
        'activate_selected' => 'Activate Selected',
        'deactivate_selected' => 'Deactivate Selected',
    ],
];
