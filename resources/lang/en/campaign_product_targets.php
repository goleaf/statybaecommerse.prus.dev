<?php

declare(strict_types=1);

return [
    'navigation' => [
        'label' => 'Campaign Targets',
    ],
    'models' => [
        'singular' => 'Campaign Target',
        'plural'   => 'Campaign Targets',
    ],
    'fields' => [
        'campaign'    => 'Campaign',
        'target_type' => 'Target Type',
        'product'     => 'Product',
        'category'    => 'Category',
        'brand'       => 'Brand',
        'collection'  => 'Collection',
        'priority'    => 'Priority',
        'weight'      => 'Weight',
        'sort_order'  => 'Sort Order',
        'is_active'   => 'Active',
        'is_featured' => 'Featured',
        'conditions'  => 'Conditions',
        'notes'       => 'Internal Notes',
    ],
    'target_types' => [
        'product'    => 'Product',
        'category'   => 'Category',
        'brand'      => 'Brand',
        'collection' => 'Collection',
        'unknown'    => 'Unknown',
    ],
    'table' => [
        'id'          => 'ID',
        'campaign'    => 'Campaign',
        'target_type' => 'Target Type',
        'target_name' => 'Target',
        'priority'    => 'Priority',
        'is_active'   => 'Active',
        'is_featured' => 'Featured',
        'created_at'  => 'Created At',
    ],
    'filters' => [
        'campaign'      => 'Campaign',
        'target_type'   => 'Target Type',
        'is_active'     => 'Active',
        'high_priority' => 'High Priority',
        'recent'        => 'Recently Added',
    ],
    'bulk_actions' => [
        'activate'   => 'Activate',
        'deactivate' => 'Deactivate',
        'feature'    => 'Feature',
        'unfeature'  => 'Remove Featured',
    ],
    'actions' => [
        'toggle_active' => 'Toggle active status',
    ],
    'notifications' => [
        'activated'        => 'Target activated successfully.',
        'deactivated'      => 'Target deactivated successfully.',
        'bulk_activated'   => 'Selected targets have been activated.',
        'bulk_deactivated' => 'Selected targets have been deactivated.',
        'bulk_featured'    => 'Selected targets have been marked as featured.',
        'bulk_unfeatured'  => 'Selected targets have been removed from featured.',
    ],
    'tabs' => [
        'all'        => 'All',
        'product'    => 'Products',
        'category'   => 'Categories',
        'brand'      => 'Brands',
        'collection' => 'Collections',
        'active'     => 'Active',
    ],
];
