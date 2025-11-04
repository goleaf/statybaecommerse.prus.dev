<?php

return [
    // Navigation
    'navigation' => [
        'label' => 'Brands',
    ],

    // Model labels
    'model' => [
        'singular' => 'Brand',
        'plural' => 'Brands',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'seo' => 'SEO Settings',
        'translations' => 'Translations',
    ],

    // Fields
    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'website' => 'Website',
        'is_enabled' => 'Enabled',
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Description',
        'translations' => 'Translations',
        'locale' => 'Language',
        'translations_count' => 'Translations',
        'products_count' => 'Products',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    // Helpers
    'helpers' => [
        'enabled' => 'Whether this brand is active and visible',
        'seo_title' => 'Recommended length: 50-60 characters',
        'seo_description' => 'Recommended length: 150-160 characters',
    ],

    // Placeholders
    'placeholders' => [
        'no_website' => 'No website',
    ],

    // Actions
    'actions' => [
        'add_translation' => 'Add Translation',
        'enable' => 'Enable',
        'disable' => 'Disable',
        'enable_selected' => 'Enable Selected',
        'disable_selected' => 'Disable Selected',
        'manage_translations' => 'Manage Translations',
        'bulk_actions' => 'Bulk Actions',
    ],

    // Filters
    'filters' => [
        'enabled_only' => 'Enabled Only',
        'has_products' => 'Has Products',
        'has_translations' => 'Has Translations',
        'translation_locale' => 'Translation Language',
    ],

    // Statistics
    'stats' => [
        'total_brands' => 'Total Brands',
        'total_brands_description' => 'All brands in the system',
        'enabled_brands' => 'Enabled Brands',
        'enabled_brands_description' => 'Active and visible brands',
        'brands_with_products' => 'Brands with Products',
        'brands_with_products_description' => 'Brands that have products',
        'brands_with_translations' => 'Brands with Translations',
        'brands_with_translations_description' => 'Brands with multi-language support',
    ],

    // Widgets
    'widgets' => [
        'brand_overview' => 'Brand Overview',
        'brand_performance' => 'Brand Performance',
        'brand_analytics' => 'Brand Analytics',
    ],

    // Empty states
    'empty_states' => [
        'no_brands' => 'No brands found',
        'no_enabled_brands' => 'No enabled brands',
        'no_brands_with_products' => 'No brands with products',
    ],

    // Messages
    'messages' => [
        'created' => 'Brand created successfully',
        'updated' => 'Brand updated successfully',
        'deleted' => 'Brand deleted successfully',
        'enabled' => 'Brand enabled successfully',
        'disabled' => 'Brand disabled successfully',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Brand name is required',
        'name_max' => 'Brand name must not exceed 255 characters',
        'slug_required' => 'Brand slug is required',
        'slug_unique' => 'Brand slug must be unique',
        'slug_alpha_dash' => 'Brand slug can only contain letters, numbers, dashes and underscores',
        'description_max' => 'Brand description must not exceed 1000 characters',
        'website_url' => 'Website must be a valid URL',
        'website_max' => 'Website must not exceed 255 characters',
        'seo_title_max' => 'SEO title must not exceed 60 characters',
        'seo_description_max' => 'SEO description must not exceed 160 characters',
    ],
];
