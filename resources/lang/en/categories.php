<?php

return [
    // Basic fields
    'title' => 'Categories',
    'single' => 'Category',
    'plural' => 'Categories',
    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'short_description' => 'Short Description',
        'parent' => 'Parent Category',
        'sort_order' => 'Sort Order',
        'is_enabled' => 'Enabled',
        'is_visible' => 'Visible',
        'is_featured' => 'Featured',
        'show_in_menu' => 'Show in Menu',
        'product_limit' => 'Product Limit',
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Description',
        'seo_keywords' => 'SEO Keywords',
        'image' => 'Image',
        'banner' => 'Banner',
        'gallery' => 'Gallery',
        'children' => 'Subcategories',
        'children_count' => 'Subcategories',
        'products_count' => 'Products',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'settings' => 'Settings',
        'media' => 'Media',
        'hierarchy' => 'Category Hierarchy',
    ],

    // Tabs
    'tabs' => [
        'translations' => 'Translations',
        'lithuanian' => 'Lithuanian',
        'english' => 'English',
    ],

    // Filters
    'filters' => [
        'is_enabled' => 'Enabled',
        'is_featured' => 'Featured',
        'is_visible' => 'Visible',
        'show_in_menu' => 'Show in Menu',
        'parent' => 'Parent Category',
        'has_children' => 'Has Subcategories',
        'with_children' => 'With Subcategories',
        'without_children' => 'Without Subcategories',
        'has_products' => 'Has Products',
        'with_products' => 'With Products',
        'without_products' => 'Without Products',
        'products_count_range' => 'Products Count Range',
        'no_products' => 'No Products',
        '1_to_10_products' => '1-10 Products',
        '11_to_50_products' => '11-50 Products',
        '51_to_100_products' => '51-100 Products',
        '100_plus_products' => '100+ Products',
        'created_from' => 'Created From',
        'created_until' => 'Created Until',
        'has_seo' => 'Has SEO',
        'root_categories' => 'Root Categories',
    ],

    // Actions
    'actions' => [
        'translate' => 'Translate',
        'view_products' => 'View Products',
        'duplicate' => 'Duplicate',
        'enable_selected' => 'Enable Selected',
        'disable_selected' => 'Disable Selected',
        'feature_selected' => 'Feature Selected',
    ],

    // Bulk Actions
    'bulk_actions' => [
        'enable_selected' => 'Enable Selected',
        'disable_selected' => 'Disable Selected',
        'feature_selected' => 'Feature Selected',
    ],

    // Messages
    'messages' => [
        'created' => 'Category created successfully',
        'updated' => 'Category updated successfully',
        'deleted' => 'Category deleted successfully',
        'status_changed' => 'Category status changed successfully',
        'featured_toggled' => 'Category featured status toggled successfully',
        'no_categories_found' => 'No categories found',
        'create_first_category' => 'Create your first category to get started',
    ],

    // Help
    'help' => [
        'create_first_category' => 'Create your first category to organize your products',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Category name is required',
        'name_max' => 'Category name must not exceed 255 characters',
        'slug_required' => 'Category slug is required',
        'slug_unique' => 'Category slug must be unique',
        'slug_alpha_dash' => 'Category slug can only contain letters, numbers, dashes and underscores',
        'description_max' => 'Category description must not exceed 1000 characters',
        'short_description_max' => 'Category short description must not exceed 500 characters',
        'seo_title_max' => 'SEO title must not exceed 255 characters',
        'seo_description_max' => 'SEO description must not exceed 500 characters',
        'seo_keywords_max' => 'SEO keywords must not exceed 255 characters',
        'sort_order_numeric' => 'Sort order must be a number',
        'product_limit_numeric' => 'Product limit must be a number',
    ],
];
