<?php

return [
    // Basic fields
    'name' => 'Name',
    'slug' => 'Slug',
    'description' => 'Description',
    'is_visible' => 'Visible',
    'is_automatic' => 'Automatic',
    'sort_order' => 'Sort Order',
    'seo_title' => 'SEO Title',
    'seo_description' => 'SEO Description',
    'is_active' => 'Active',
    'rules' => 'Rules',
    'max_products' => 'Max Products',
    'meta_title' => 'Meta Title',
    'meta_description' => 'Meta Description',
    'meta_keywords' => 'Meta Keywords',
    'display_type' => 'Display Type',
    'products_per_page' => 'Products Per Page',
    'show_filters' => 'Show Filters',
    'image' => 'Image',
    'banner' => 'Banner',

    // Helpers
    'full_display_name' => 'Full Display Name',
    'collection_info' => 'Collection Information',
    'seo_info' => 'SEO Information',
    'business_info' => 'Business Information',
    'complete_info' => 'Complete Information',
    'products_count' => 'Products Count',
    'type' => 'Type',
    'automatic' => 'Automatic',
    'manual' => 'Manual',

    // Filters
    'filters' => [
        'is_visible' => 'Visibility',
        'is_automatic' => 'Type',
        'has_products' => 'Has Products',
        'created_from' => 'Created From',
        'created_until' => 'Created Until',
        'display_type' => 'Display Type',
        'show_filters' => 'Show Filters',
    ],

    // Actions
    'actions' => [
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'toggle_visibility' => 'Toggle Visibility',
        'manage_products' => 'Manage Products',
    ],

    // Confirmations
    'confirmations' => [
        'toggle_visibility' => 'Are you sure you want to toggle the visibility of this collection?',
        'delete' => 'Are you sure you want to delete this collection? This action cannot be undone.',
    ],

    // Empty states
    'empty_states' => [
        'no_collections' => 'No collections found',
        'no_products' => 'No products in this collection',
        'no_translations' => 'No translations available',
    ],

    // Messages
    'messages' => [
        'created' => 'Collection created successfully',
        'updated' => 'Collection updated successfully',
        'deleted' => 'Collection deleted successfully',
        'visibility_toggled' => 'Collection visibility toggled successfully',
        'products_managed' => 'Products managed successfully',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Name is required',
        'slug_required' => 'Slug is required',
        'slug_unique' => 'Slug must be unique',
        'description_max' => 'Description cannot exceed 1000 characters',
        'max_products_numeric' => 'Max products must be a number',
        'sort_order_numeric' => 'Sort order must be a number',
    ],

    // Statistics
    'stats' => [
        'total_collections' => 'Total Collections',
        'visible_collections' => 'Visible Collections',
        'automatic_collections' => 'Automatic Collections',
        'manual_collections' => 'Manual Collections',
        'collections_with_products' => 'Collections with Products',
        'avg_products_per_collection' => 'Average Products per Collection',
        'all_collections' => 'All collections in the system',
        'visible_to_customers' => 'Visible to customers',
        'auto_generated' => 'Auto-generated collections',
        'manually_created' => 'Manually created collections',
        'have_products' => 'Collections that have products',
        'average_products' => 'Average number of products',
    ],

    // Widgets
    'widgets' => [
        'stats_heading' => 'Collection Statistics',
        'performance_heading' => 'Collection Performance',
        'products_heading' => 'Collections with Products',
        'charts' => [
            'products_count' => 'Products Count',
        ],
    ],

    // Display types
    'display_types' => [
        'grid' => 'Grid',
        'list' => 'List',
        'carousel' => 'Carousel',
    ],

    // Status
    'status' => [
        'visible' => 'Visible',
        'hidden' => 'Hidden',
    ],

    // Types
    'types' => [
        'automatic' => 'Automatic',
        'manual' => 'Manual',
    ],

    // Placeholders
    'placeholders' => [
        'name' => 'Enter collection name',
        'slug' => 'Enter collection slug',
        'description' => 'Enter collection description',
        'seo_title' => 'Enter SEO title',
        'seo_description' => 'Enter SEO description',
        'meta_title' => 'Enter meta title',
        'meta_description' => 'Enter meta description',
        'meta_keywords' => 'Enter meta keywords (comma separated)',
    ],

    // Help text
    'help' => [
        'slug' => 'URL-friendly version of the name',
        'is_visible' => 'Whether the collection is visible to customers',
        'is_automatic' => 'Whether the collection is automatically generated',
        'sort_order' => 'Order in which collections are displayed',
        'max_products' => 'Maximum number of products in this collection',
        'rules' => 'Rules for automatic collection generation',
        'display_type' => 'How products are displayed in this collection',
        'products_per_page' => 'Number of products per page',
        'show_filters' => 'Whether to show product filters',
        'meta_keywords' => 'Comma-separated keywords for SEO',
    ],
];
