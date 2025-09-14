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
        'settings' => 'Settings',
        'all' => 'All Variants',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
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
        'settings' => 'Variant Settings',
    ],

    // Fields
    'fields' => [
        'product' => 'Product',
        'name' => 'Variant Name',
        'sku' => 'SKU',
        'variant_sku_suffix' => 'SKU Suffix',
        'barcode' => 'Barcode',
        'variant_type' => 'Variant Type',
        'is_default_variant' => 'Default Variant',
        'size' => 'Size',
        'size_unit' => 'Size Unit',
        'size_display' => 'Size Display',
        'size_price_modifier' => 'Size Price Modifier',
        'size_weight_modifier' => 'Size Weight Modifier',
        'price' => 'Price',
        'compare_price' => 'Compare Price',
        'cost_price' => 'Cost Price',
        'track_inventory' => 'Track Inventory',
        'allow_backorder' => 'Allow Backorder',
        'quantity' => 'Quantity',
        'low_stock_threshold' => 'Low Stock Threshold',
        'attribute' => 'Attribute',
        'attribute_value' => 'Attribute Value',
        'image' => 'Image',
        'alt_text' => 'Alt Text',
        'sort_order' => 'Sort Order',
        'is_primary' => 'Primary Image',
        'is_enabled' => 'Enabled',
        'position' => 'Position',
        'variant_metadata' => 'Variant Metadata',
        'metadata_key' => 'Key',
        'metadata_value' => 'Value',
        'stock_status' => 'Stock Status',
        'created_at' => 'Created At',
    ],

    // Variant Types
    'variant_types' => [
        'size' => 'Size',
        'color' => 'Color',
        'material' => 'Material',
        'style' => 'Style',
        'custom' => 'Custom',
    ],

    // Stock Status
    'stock_status' => [
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'not_tracked' => 'Not Tracked',
    ],

    // Actions
    'actions' => [
        'add_attribute' => 'Add Attribute',
        'add_image' => 'Add Image',
        'add_metadata' => 'Add Metadata',
        'set_default' => 'Set as Default',
        'enable' => 'Enable',
        'disable' => 'Disable',
    ],

    // Messages
    'messages' => [
        'created_successfully' => 'Product variant created successfully',
        'created_successfully_description' => 'The product variant has been created and is ready to use',
        'updated_successfully' => 'Product variant updated successfully',
        'updated_successfully_description' => 'The product variant has been updated with your changes',
        'set_as_default_success' => 'Variant set as default successfully',
        'bulk_enable_success' => 'Selected variants have been enabled',
        'bulk_disable_success' => 'Selected variants have been disabled',
    ],

    // Validation Messages
    'validation' => [
        'name_required' => 'Variant name is required',
        'sku_required' => 'SKU is required',
        'sku_unique' => 'This SKU is already in use',
        'product_required' => 'Product is required',
        'price_required' => 'Price is required',
        'price_numeric' => 'Price must be a number',
        'quantity_numeric' => 'Quantity must be a number',
    ],

    // Help Text
    'help' => [
        'variant_sku_suffix' => 'Optional suffix to append to the base SKU',
        'size_price_modifier' => 'Additional price for this size (can be negative for discounts)',
        'size_weight_modifier' => 'Additional weight for this size',
        'low_stock_threshold' => 'Alert when stock falls below this number',
        'variant_metadata' => 'Additional custom data for this variant',
    ],

    // Frontend Messages
    'messages' => [
        'select_variant' => 'Please select a variant',
        'no_variant_selected' => 'Please select a variant before adding to cart',
        'variant_not_available' => 'This variant is not available for purchase',
        'insufficient_stock' => 'Insufficient stock for the selected quantity',
        'added_to_cart' => 'Product added to cart successfully',
        'not_available' => 'Not Available',
        'out_of_stock' => 'Out of Stock',
        'low_stock' => 'Only :quantity left in stock',
        'in_stock' => ':quantity available',
    ],

    // Frontend Actions
    'actions' => [
        'add_to_cart' => 'Add to Cart',
    ],
];
