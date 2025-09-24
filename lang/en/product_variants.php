<?php

declare(strict_types=1);

return [
    // Navigation and Labels
    'title' => 'Product Variants',
    'plural' => 'Product Variants',
    'single' => 'Product Variant',
    'navigation_label' => 'Product Variants',
    'navigation_group' => 'Products',

    // Tabs
    'tabs' => [
        'main' => 'Main Information',
        'basic_information' => 'Basic Information',
        'size_information' => 'Size Information',
        'pricing' => 'Pricing',
        'inventory' => 'Inventory',
        'attributes' => 'Attributes',
        'images' => 'Images',
        'analytics' => 'Analytics',
        'seo' => 'SEO',
        'settings' => 'Settings',
        'all' => 'All Variants',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'on_sale' => 'On Sale',
        'featured' => 'Featured',
        'new' => 'New',
        'bestsellers' => 'Bestsellers',
        'size_variants' => 'Size Variants',
        'color_variants' => 'Color Variants',
        'default_variants' => 'Default Variants',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'size_information' => 'Size Information',
        'pricing' => 'Pricing Information',
        'inventory' => 'Inventory Management',
        'attributes' => 'Variant Attributes',
        'images' => 'Variant Images',
        'analytics' => 'Analytics Data',
        'seo' => 'SEO Settings',
        'settings' => 'Variant Settings',
    ],

    // Fields
    'fields' => [
        'product' => 'Product',
        'name' => 'Variant Name',
        'variant_name_lt' => 'Variant Name (LT)',
        'variant_name_en' => 'Variant Name (EN)',
        'description_lt' => 'Description (LT)',
        'description_en' => 'Description (EN)',
        'sku' => 'SKU',
        'variant_sku_suffix' => 'Variant SKU Suffix',
        'barcode' => 'Barcode',
        'variant_type' => 'Variant Type',
        'is_default_variant' => 'Is Default Variant',
        'size' => 'Size',
        'size_unit' => 'Size Unit',
        'size_display' => 'Size Display',
        'size_price_modifier' => 'Size Price Modifier',
        'size_weight_modifier' => 'Size Weight Modifier',
        'price' => 'Price',
        'compare_price' => 'Compare Price',
        'cost_price' => 'Cost Price',
        'wholesale_price' => 'Wholesale Price',
        'member_price' => 'Member Price',
        'promotional_price' => 'Promotional Price',
        'is_on_sale' => 'Is On Sale',
        'sale_start_date' => 'Sale Start Date',
        'sale_end_date' => 'Sale End Date',
        'stock_quantity' => 'Stock Quantity',
        'reserved_quantity' => 'Reserved Quantity',
        'available_quantity' => 'Available Quantity',
        'sold_quantity' => 'Sold Quantity',
        'weight' => 'Weight',
        'track_inventory' => 'Track Inventory',
        'is_enabled' => 'Is Enabled',
        'attributes' => 'Attributes',
        'images' => 'Images',
        'seo_title_lt' => 'SEO Title (LT)',
        'seo_title_en' => 'SEO Title (EN)',
        'seo_description_lt' => 'SEO Description (LT)',
        'seo_description_en' => 'SEO Description (EN)',
        'views_count' => 'Views Count',
        'clicks_count' => 'Clicks Count',
        'conversion_rate' => 'Conversion Rate',
        'is_featured' => 'Is Featured',
        'is_new' => 'Is New',
        'is_bestseller' => 'Is Bestseller',
        'variant_combination_hash' => 'Variant Combination Hash',
        'stock_status' => 'Stock Status',
        'description' => 'Description',
        'quantity' => 'Quantity',
        'price_type' => 'Price Type',
        'update_type' => 'Update Type',
        'update_value' => 'Update Value',
        'change_reason' => 'Change Reason',
        'apply_to_sale_items' => 'Apply to Sale Items',
        'update_compare_price' => 'Update Compare Price',
        'compare_price_action' => 'Compare Price Action',
        'compare_price_value' => 'Compare Price Value',
        'set_sale_period' => 'Set Sale Period',
        'rating' => 'Rating',
        'available' => 'Available',
        'badges' => 'Badges',
    ],

    // Variant Types
    'variant_types' => [
        'size' => 'Size',
        'color' => 'Color',
        'material' => 'Material',
        'style' => 'Style',
        'custom' => 'Custom',
    ],

    // Messages
    'messages' => [
        'no_variant_selected' => 'No variant selected.',
        'variant_not_available' => 'This variant is not available for purchase at the moment.',
        'insufficient_stock' => 'Insufficient stock for the selected quantity.',
        'added_to_cart' => 'Item added to cart!',
        'select_variant' => 'Select a variant to see details.',
        'out_of_stock' => 'Out of Stock',
        'low_stock' => 'Low Stock (only :quantity left)',
        'in_stock' => 'In Stock ( :quantity available)',
        'max_quantity' => 'Maximum quantity',
    ],

    // Frontend Actions
    'actions' => [
        'bulk_price_update' => 'Bulk Price Update',
        'export' => 'Export',
        'import' => 'Import',
        'add_to_cart' => 'Add to Cart',
        'add_to_comparison' => 'Add to Comparison',
        'compare' => 'Compare',
        'view_details' => 'View Details',
        'actions' => 'Actions',
    ],

    // Price Types
    'price_types' => [
        'regular' => 'Regular Price',
        'wholesale' => 'Wholesale Price',
        'member' => 'Member Price',
        'promotional' => 'Promotional Price',
    ],

    // Update Types
    'update_types' => [
        'fixed_amount' => 'Fixed Amount',
        'percentage' => 'Percentage',
        'multiply_by' => 'Multiply By',
        'set_to' => 'Set To',
    ],

    // Compare Price Actions
    'compare_price_actions' => [
        'no_change' => 'No Change',
        'match_new_price' => 'Match New Price',
        'increase_by_percentage' => 'Increase by Percentage',
        'increase_by_fixed_amount' => 'Increase by Fixed Amount',
    ],

    // Help Text
    'help' => [
        'update_value' => 'Enter a number. E.g., 10 (for percentage) or 5.50 (for amount)',
    ],

    // Placeholders
    'placeholders' => [
        'change_reason' => 'Enter change reason (optional)',
    ],

    // Modals
    'modals' => [
        'bulk_price_update_heading' => 'Bulk Price Update',
        'bulk_price_update_description' => 'You will update prices for all selected variants.',
    ],

    // Notifications
    'notifications' => [
        'bulk_update_success' => 'Prices Successfully Updated',
        'bulk_update_success_body' => 'Updated prices for :updated variants. Skipped :skipped variants.',
    ],

    // Stats
    'stats' => [
        'total_variants' => 'Total Variants',
        'all_variants' => 'All Variants',
        'in_stock' => 'In Stock',
        'available_variants' => 'Available Variants',
        'low_stock' => 'Low Stock',
        'need_restocking' => 'Need Restocking',
        'out_of_stock' => 'Out of Stock',
        'unavailable_variants' => 'Unavailable Variants',
        'total_views' => 'Total Views',
        'product_page_views' => 'Product Page Views',
        'total_clicks' => 'Total Clicks',
        'variant_selections' => 'Variant Selections',
        'conversion_rate' => 'Conversion Rate',
        'views_to_sales' => 'Views to Sales',
        'total_stock' => 'Total Stock',
        'all_variants_stock' => 'All Variants',
        'available_stock' => 'Available Stock',
        'ready_for_sale' => 'Ready for Sale',
        'reserved_stock' => 'Reserved Stock',
        'pending_orders' => 'Pending Orders',
        'sold_stock' => 'Sold Stock',
        'total_sold' => 'Total Sold',
        'low_stock_alerts' => 'Low Stock Alerts',
        'stock_value' => 'Stock Value',
        'total_inventory_value' => 'Total Inventory Value',
        'average_price' => 'Average Price',
        'highest_price' => 'Highest Price',
        'most_expensive' => 'Most Expensive',
        'lowest_price' => 'Lowest Price',
        'most_affordable' => 'Most Affordable',
        'on_sale' => 'On Sale',
        'discounted_variants' => 'Discounted Variants',
        'average_discount' => 'Average Discount',
        'sale_discount' => 'Sale Discount',
        'total_revenue' => 'Total Revenue',
        'from_sales' => 'From Sales',
        'price_range_under_50' => 'Under €50',
        'under_50_euros' => 'Under 50 Euros',
        'price_range_50_100' => '€50-100',
        'between_50_100_euros' => 'Between 50-100 Euros',
    ],

    // Comparison
    'comparison' => [
        'title' => 'Variant Comparison',
        'subtitle' => 'Comparing :count variants',
        'clear_all' => 'Clear All',
        'variant' => 'Variant',
        'remove' => 'Remove',
        'no_variants_selected' => 'No Variants Selected',
        'select_variants_to_compare' => 'Select variants to compare',
    ],

    // Stock Status
    'stock_status' => [
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'not_tracked' => 'Not Tracked',
    ],

    // Badges
    'badges' => [
        'new' => 'New',
        'featured' => 'Featured',
        'bestseller' => 'Bestseller',
        'sale' => 'Sale',
    ],

    // Showcase
    'showcase' => [
        'title' => 'Product Variants Showcase',
        'subtitle' => 'See all possible variant features and capabilities',
        'select_product' => 'Select Product',
        'variants_count' => 'variants',
        'brand' => 'Brand',
        'analytics_title' => 'Analytics Data',
        'total_variants' => 'Total Variants',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'on_sale' => 'On Sale',
        'featured' => 'Featured',
        'average_price' => 'Average Price',
        'highest_price' => 'Highest Price',
        'lowest_price' => 'Lowest Price',
        'variant_selection' => 'Variant Selection',
        'selected_variant' => 'Selected Variant',
        'variant_attributes' => 'Variant Attributes',
        'all_variants' => 'All Variants',
    ],
];
