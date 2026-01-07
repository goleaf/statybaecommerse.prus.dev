<?php

declare(strict_types=1);

return [
    // Navigation
    'navigation' => [
        'label' => 'Brands',
    ],

    // Model labels
    'model' => [
        'singular' => 'Brand',
        'plural'   => 'Brands',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'media'             => 'Media',
        'seo'               => 'SEO Settings',
        'settings'          => 'Settings',
        'social'            => 'Social Profiles',
        'translations'      => 'Translations',
    ],

    // Fields
    'fields' => [
        'name'               => 'Name',
        'slug'               => 'Slug',
        'description'        => 'Description',
        'website'            => 'Website',
        'is_enabled'         => 'Enabled',
        'is_active'          => 'Active',
        'is_visible'         => 'Visible',
        'is_featured'        => 'Featured',
        'is_premium'         => 'Premium',
        'logo'               => 'Logo',
        'banner'             => 'Banner',
        'seo_title'          => 'SEO Title',
        'seo_description'    => 'SEO Description',
        'social_links'       => 'Social Links',
        'social_platform'    => 'Platform',
        'social_url'         => 'Profile URL',
        'translations'       => 'Translations',
        'locale'             => 'Language',
        'translations_count' => 'Translations',
        'products_count'     => 'Products',
        'created_at'         => 'Created At',
        'updated_at'         => 'Updated At',
    ],

    // Helpers
    'helpers' => [
        'enabled'         => 'Whether this brand is active and visible',
        'seo_title'       => 'Recommended length: 50-60 characters',
        'seo_description' => 'Recommended length: 150-160 characters',
        'website'         => 'Public URL pointing to the official brand website',
        'social_links'    => 'Add verified social media profiles that will be stored as JSON',
        'is_premium'      => 'Premium brands receive highlighted placement on landing pages',
    ],

    // Placeholders
    'placeholders' => [
        'no_website' => 'No website',
    ],

    // Actions
    'actions' => [
        'add_translation'         => 'Add Translation',
        'enable'                  => 'Enable',
        'disable'                 => 'Disable',
        'enable_selected'         => 'Enable Selected',
        'disable_selected'        => 'Disable Selected',
        'activate'                => 'Activate',
        'deactivate'              => 'Deactivate',
        'feature'                 => 'Feature',
        'unfeature'               => 'Unfeature',
        'feature_selected'        => 'Feature Selected',
        'unfeature_selected'      => 'Unfeature Selected',
        'mark_premium'            => 'Mark as Premium',
        'unmark_premium'          => 'Remove Premium',
        'mark_premium_selected'   => 'Mark Premium (Selected)',
        'unmark_premium_selected' => 'Remove Premium (Selected)',
        'add_social_link'         => 'Add Social Link',
        'manage_translations'     => 'Manage Translations',
        'bulk_actions'            => 'Bulk Actions',
    ],

    // Filters
    'filters' => [
        'enabled_only'       => 'Enabled Only',
        'featured_only'      => 'Featured Only',
        'not_featured'       => 'Not Featured',
        'premium_only'       => 'Premium Only',
        'not_premium'        => 'Not Premium',
        'visible_only'       => 'Visible Only',
        'hidden_only'        => 'Hidden Only',
        'with_products'      => 'With Products',
        'without_products'   => 'Without Products',
        'with_website'       => 'With Website',
        'recent'             => 'Recent',
        'has_products'       => 'Has Products',
        'has_translations'   => 'Has Translations',
        'translation_locale' => 'Translation Language',
    ],

    // Notifications
    'notifications' => [
        'activated'             => 'Brand activated successfully',
        'deactivated'           => 'Brand deactivated successfully',
        'featured_enabled'      => 'Brand marked as featured',
        'featured_disabled'     => 'Brand unfeatured successfully',
        'bulk_enabled'          => 'Selected brands enabled successfully',
        'bulk_disabled'         => 'Selected brands disabled successfully',
        'bulk_featured'         => 'Selected brands featured successfully',
        'bulk_unfeatured'       => 'Selected brands unfeatured successfully',
        'premium_enabled'       => 'Brand marked as premium',
        'premium_disabled'      => 'Brand removed from premium list',
        'bulk_premium_enabled'  => 'Selected brands marked as premium',
        'bulk_premium_disabled' => 'Selected brands removed from premium list',
    ],

    // Statistics
    'stats' => [
        'total_brands'                         => 'Total Brands',
        'total_brands_description'             => 'All brands in the system',
        'enabled_brands'                       => 'Enabled Brands',
        'enabled_brands_description'           => 'Active and visible brands',
        'brands_with_products'                 => 'Brands with Products',
        'brands_with_products_description'     => 'Brands that have products',
        'brands_with_translations'             => 'Brands with Translations',
        'brands_with_translations_description' => 'Brands with multi-language support',
    ],

    // Widgets
    'widgets' => [
        'brand_overview'    => 'Brand Overview',
        'brand_performance' => 'Brand Performance',
        'brand_analytics'   => 'Brand Analytics',
    ],

    // Empty states
    'empty_states' => [
        'no_brands'               => 'No brands found',
        'no_enabled_brands'       => 'No enabled brands',
        'no_brands_with_products' => 'No brands with products',
    ],

    // Messages
    'messages' => [
        'created'  => 'Brand created successfully',
        'updated'  => 'Brand updated successfully',
        'deleted'  => 'Brand deleted successfully',
        'enabled'  => 'Brand enabled successfully',
        'disabled' => 'Brand disabled successfully',
    ],

    // Validation
    'validation' => [
        'name_required'       => 'Brand name is required',
        'name_max'            => 'Brand name must not exceed 255 characters',
        'slug_required'       => 'Brand slug is required',
        'slug_unique'         => 'Brand slug must be unique',
        'slug_alpha_dash'     => 'Brand slug can only contain letters, numbers, dashes and underscores',
        'description_max'     => 'Brand description must not exceed 1000 characters',
        'website_url'         => 'Website must be a valid URL',
        'website_max'         => 'Website must not exceed 255 characters',
        'seo_title_max'       => 'SEO title must not exceed 60 characters',
        'seo_description_max' => 'SEO description must not exceed 160 characters',
    ],

    // Social platform labels
    'social' => [
        'platforms' => [
            'facebook'  => 'Facebook',
            'instagram' => 'Instagram',
            'linkedin'  => 'LinkedIn',
            'tiktok'    => 'TikTok',
            'twitter'   => 'Twitter',
            'youtube'   => 'YouTube',
            'pinterest' => 'Pinterest',
            'github'    => 'GitHub',
            'website'   => 'Website',
        ],
    ],
];
