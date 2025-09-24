<?php

return [
    'title' => 'Recommendation Configurations',
    'plural' => 'Recommendation Configurations',
    'single' => 'Recommendation Configuration',

    'basic_information' => 'Basic Information',
    'name' => 'Name',
    'type' => 'Type',
    'description' => 'Description',

    'algorithm_types' => [
        'collaborative' => 'Collaborative Filtering',
        'content_based' => 'Content-Based Filtering',
        'hybrid' => 'Hybrid Recommendation',
        'popularity' => 'Popularity-Based',
        'trending' => 'Trending Products',
        'similarity' => 'Similarity-Based',
        'custom' => 'Custom Algorithm',
    ],

    'algorithm_settings' => 'Algorithm Settings',
    'min_score' => 'Minimum Score',
    'max_results' => 'Maximum Results',
    'decay_factor' => 'Decay Factor',
    'priority' => 'Priority',

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
    'cache_ttl' => 'Cache TTL (minutes)',
    'sort_order' => 'Sort Order',
    'notes' => 'Notes',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    'products_count' => 'Products Count',
    'categories_count' => 'Categories Count',

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
