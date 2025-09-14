<?php

return [
    'title' => 'Collections',
    
    'sections' => [
        'basic_information' => 'Basic Information',
        'collection_settings' => 'Collection Settings',
        'seo_settings' => 'SEO Settings',
        'media' => 'Media',
    ],
    
    'fields' => [
        'name' => 'Name',
        'slug' => 'URL slug',
        'description' => 'Description',
        'is_visible' => 'Visible to customers',
        'is_automatic' => 'Automatic collection',
        'sort_order' => 'Sort order',
        'max_products' => 'Maximum products',
        'rules' => 'Rules',
        'rule_key' => 'Rule key',
        'rule_value' => 'Rule value',
        'seo_title' => 'SEO title',
        'seo_description' => 'SEO description',
        'meta_title' => 'Meta title',
        'meta_description' => 'Meta description',
        'meta_keywords' => 'Meta keywords',
        'display_type' => 'Display type',
        'products_per_page' => 'Products per page',
        'show_filters' => 'Show filters',
        'image' => 'Image',
        'banner' => 'Banner',
    ],
    
    'placeholders' => [
        'name' => 'Enter collection name',
        'slug' => 'collection-url',
        'description' => 'Enter collection description',
        'seo_title' => 'SEO title',
        'seo_description' => 'SEO description',
        'meta_title' => 'Meta title',
        'meta_description' => 'Meta description',
        'meta_keywords' => 'keyword1, keyword2, keyword3',
    ],
    
    'help' => [
        'slug' => 'URL part that will be used for the collection page',
        'is_visible' => 'Whether the collection will be visible to customers',
        'is_automatic' => 'Whether the collection is generated automatically based on rules',
        'sort_order' => 'Number by which collections are sorted',
        'max_products' => 'Maximum number of products in collection (0 = unlimited)',
        'rules' => 'Rules for automatic collection generation',
        'meta_keywords' => 'Keywords separated by commas',
        'display_type' => 'How to display products in the collection',
        'products_per_page' => 'How many products to show per page',
        'show_filters' => 'Whether to show filters on the collection page',
    ],
    
    'display_types' => [
        'grid' => 'Grid',
        'list' => 'List',
        'carousel' => 'Carousel',
    ],
    
    'table' => [
        'name' => 'Name',
        'slug' => 'URL slug',
        'description' => 'Description',
        'is_visible' => 'Visible',
        'is_automatic' => 'Automatic',
        'products_count' => 'Products',
        'sort_order' => 'Sort',
        'display_type' => 'Display type',
        'products_per_page' => 'Per page',
        'show_filters' => 'Filters',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'image' => 'Image',
    ],
    
    'filters' => [
        'is_visible' => 'Visibility',
        'is_automatic' => 'Type',
        'has_products' => 'Has products',
        'created_from' => 'Created from',
        'created_until' => 'Created until',
        'display_type' => 'Display type',
        'show_filters' => 'Shows filters',
    ],
    
    'status' => [
        'visible' => 'Visible',
        'hidden' => 'Hidden',
    ],
    
    'types' => [
        'manual' => 'Manual',
        'automatic' => 'Automatic',
    ],
    
    'actions' => [
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'toggle_visibility' => 'Toggle visibility',
        'manage_products' => 'Manage products',
    ],
    
    'confirmations' => [
        'toggle_visibility' => 'Are you sure you want to change the collection visibility?',
        'delete' => 'Are you sure you want to delete this collection?',
    ],
    
    'stats' => [
        'total_collections' => 'Total Collections',
        'all_collections' => 'All collections',
        'visible_collections' => 'Visible Collections',
        'visible_to_customers' => 'Visible to customers',
        'automatic_collections' => 'Automatic Collections',
        'auto_generated' => 'Auto generated',
        'manual_collections' => 'Manual Collections',
        'manually_created' => 'Manually created',
        'collections_with_products' => 'Collections with Products',
        'have_products' => 'Have products',
        'avg_products_per_collection' => 'Avg Products per Collection',
        'average_products' => 'Average products',
    ],
    
    'charts' => [
        'performance_heading' => 'Collection Performance',
        'products_count' => 'Products count',
    ],
    
    'widgets' => [
        'products_heading' => 'Collections with Products',
    ],
];
