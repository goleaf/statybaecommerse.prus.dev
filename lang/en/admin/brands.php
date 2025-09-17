<?php

return [
    'navigation' => [
        'label' => 'Brands',
    ],

    'model' => [
        'singular' => 'Brand',
        'plural' => 'Brands',
    ],

    'tabs' => [
        'basic_information' => 'Basic Information',
        'seo' => 'SEO',
        'translations' => 'Translations',
        'with_products' => 'With Products',
        'without_products' => 'Without Products',
    ],

    'sections' => [
        'basic_information' => 'Basic Information',
        'media' => 'Media',
        'seo' => 'SEO Settings',
        'translations' => 'Translations',
        'description' => 'Description',
        'statistics' => 'Statistics',
        'timestamps' => 'Timestamps',
    ],

    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'website' => 'Website',
        'is_enabled' => 'Enabled',
        'logo' => 'Logo',
        'banner' => 'Banner',
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Description',
        'translations' => 'Translations',
        'locale' => 'Locale',
        'products_count' => 'Products Count',
        'active_products_count' => 'Active Products Count',
        'translations_count' => 'Translations Count',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'helpers' => [
        'seo_title' => 'Recommended length: 50-60 characters',
        'seo_description' => 'Recommended length: 150-160 characters',
        'enabled' => 'Enabled brands will be displayed on the website',
    ],

    'placeholders' => [
        'no_website' => 'No website',
        'no_description' => 'No description',
    ],

    'filters' => [
        'enabled' => 'Enabled Status',
        'all_brands' => 'All Brands',
        'enabled_only' => 'Enabled Only',
        'disabled_only' => 'Disabled Only',
        'has_products' => 'Has Products',
        'has_website' => 'Has Website',
        'has_logo' => 'Has Logo',
        'has_banner' => 'Has Banner',
        'has_translations' => 'Has Translations',
        'translation_locale' => 'Translation Locale',
        'created_from' => 'Created From',
        'created_until' => 'Created Until',
    ],

    'actions' => [
        'create' => 'Create Brand',
        'create_first_brand' => 'Create First Brand',
        'add_translation' => 'Add Translation',
        'enable_selected' => 'Enable Selected',
        'disable_selected' => 'Disable Selected',
        'enable' => 'Enable',
        'disable' => 'Disable',
        'manage_translations' => 'Manage Translations',
        'bulk_actions' => 'Bulk Actions',
    ],

    'messages' => [
        'slug_copied' => 'Slug copied to clipboard',
    ],

    'notifications' => [
        'created' => 'Brand created successfully',
        'created_description' => 'Brand ":name" has been created successfully.',
        'updated' => 'Brand updated successfully',
        'updated_description' => 'Brand ":name" has been updated successfully.',
        'deleted' => 'Brand deleted successfully',
    ],

    'empty_state' => [
        'heading' => 'No brands found',
        'description' => 'Get started by creating your first brand.',
    ],

    'stats' => [
        'total_brands' => 'Total Brands',
        'total_brands_description' => 'Total number of brands in the system',
        'enabled_brands' => 'Enabled Brands',
        'enabled_brands_description' => 'Brands that are displayed on the website',
        'brands_with_products' => 'With Products',
        'brands_with_products_description' => 'Brands that have products',
        'brands_with_translations' => 'With Translations',
        'brands_with_translations_description' => 'Brands with translations',
    ],

    'widgets' => [
        'overview_heading' => 'Latest Brands',
        'performance_heading' => 'Brand Performance',
        'products_count' => 'Products Count',
        'translations_count' => 'Translations Count',
    ],
];
