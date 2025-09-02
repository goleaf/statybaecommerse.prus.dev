<?php declare(strict_types=1);

return [
    // Navigation
    'navigation' => [
        'dashboard' => 'Dashboard',
        'products' => 'Products',
        'categories' => 'Categories',
        'brands' => 'Brands',
        'collections' => 'Collections',
        'attributes' => 'Attributes',
        'orders' => 'Orders',
        'customers' => 'Customers',
        'users' => 'Users',
        'discounts' => 'Discounts',
        'campaigns' => 'Campaigns',
        'coupons' => 'Coupons',
        'partners' => 'Partners',
        'partner_tiers' => 'Partner Tiers',
        'customer_groups' => 'Customer Groups',
        'reviews' => 'Reviews',
        'locations' => 'Locations',
        'zones' => 'Zones',
        'countries' => 'Countries',
        'currencies' => 'Currencies',
        'settings' => 'Settings',
        'activity_log' => 'Activity Log',
        'media' => 'Media',
        'documents' => 'Documents',
        'document_templates' => 'Document Templates',
        'legals' => 'Legal Documents',
        'addresses' => 'Addresses',
    ],

    // Models
    'models' => [
        'product' => 'Product',
        'products' => 'Products',
        'category' => 'Category',
        'categories' => 'Categories',
        'brand' => 'Brand',
        'brands' => 'Brands',
        'collection' => 'Collection',
        'collections' => 'Collections',
        'order' => 'Order',
        'orders' => 'Orders',
        'customer' => 'Customer',
        'customers' => 'Customers',
        'user' => 'User',
        'users' => 'Users',
        'discount' => 'Discount',
        'discounts' => 'Discounts',
        'setting' => 'Setting',
        'settings' => 'Settings',
    ],

    // Fields
    'fields' => [
        'id' => 'ID',
        'name' => 'Name',
        'title' => 'Title',
        'slug' => 'Slug',
        'description' => 'Description',
        'content' => 'Content',
        'price' => 'Price',
        'sku' => 'SKU',
        'stock_quantity' => 'Stock Quantity',
        'is_visible' => 'Visible',
        'is_active' => 'Active',
        'is_public' => 'Public',
        'is_encrypted' => 'Encrypted',
        'status' => 'Status',
        'type' => 'Type',
        'value' => 'Value',
        'key' => 'Key',
        'group' => 'Group',
        'sort_order' => 'Sort Order',
        'validation_rules' => 'Validation Rules',
        'email' => 'Email',
        'phone' => 'Phone',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'public' => 'Public',
        'encrypted' => 'Encrypted',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'advanced_options' => 'Advanced Options',
        'value_configuration' => 'Value Configuration',
        'seo_information' => 'SEO Information',
    ],

    // Setting Types
    'setting_types' => [
        'text' => 'Text',
        'number' => 'Number',
        'boolean' => 'Boolean',
        'json' => 'JSON',
        'array' => 'Array',
    ],

    // Help Text
    'help' => [
        'json_format' => 'Enter valid JSON format',
        'public_setting' => 'Setting will be visible publicly',
        'encrypted_setting' => 'Value will be encrypted in database',
        'validation_rules' => 'Laravel validation rules',
        'slug_auto_generated' => 'Auto-generated from name if left empty',
        'seo_title_help' => 'Optimal length: 50-60 characters',
        'seo_description_help' => 'Optimal length: 150-160 characters',
    ],

    // Quick Actions
    'quick_actions' => [
        'create' => 'Create',
        'create_product' => 'Create Product',
        'create_order' => 'Create Order',
        'create_user' => 'Create User',
        'manage' => 'Manage',
        'view_products' => 'View Products',
        'view_orders' => 'View Orders',
        'view_users' => 'View Users',
    ],

    // Widgets
    'widgets' => [
        'quick_actions' => 'Quick Actions',
        'system_health' => 'System Health',
        'analytics' => 'Analytics',
        'recent_activity' => 'Recent Activity',
        'low_stock_alerts' => 'Low Stock Alerts',
    ],

    // Stats
    'stats' => [
        'total_orders' => 'Total Orders',
        'orders_this_month' => 'Orders This Month',
        'total_revenue' => 'Total Revenue',
        'revenue_this_month' => 'Revenue This Month',
        'active_products' => 'Active Products',
        'products_in_stock' => 'Products In Stock',
        'registered_users' => 'Registered Users',
        'new_users_this_week' => 'New Users This Week',
    ],
];