<?php

return [
    // Basic Information
    'name' => 'Name',
    'slug' => 'URL Slug',
    'code' => 'Code',
    'description' => 'Description',
    
    // Configuration
    'currency' => 'Currency',
    'tax_rate' => 'Tax Rate',
    'shipping_rate' => 'Shipping Rate',
    'sort_order' => 'Sort Order',
    
    // Status
    'is_enabled' => 'Enabled',
    'is_default' => 'Default',
    
    // Relations
    'countries' => 'Countries',
    'countries_count' => 'Countries Count',
    'regions' => 'Regions',
    'cities' => 'Cities',
    'orders' => 'Orders',
    'price_lists' => 'Price Lists',
    'discounts' => 'Discounts',
    
    // Translations
    'translations' => 'Translations',
    'locale' => 'Locale',
    'add_translation' => 'Add Translation',
    
    // Metadata
    'metadata' => 'Metadata',
    'key' => 'Key',
    'value' => 'Value',
    
    // Timestamps
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'deleted_at' => 'Deleted At',
    
    // Actions
    'create_zone' => 'Create Zone',
    'edit_zone' => 'Edit Zone',
    'view_zone' => 'View Zone',
    'delete_zone' => 'Delete Zone',
    'duplicate_zone' => 'Duplicate Zone',
    
    // Messages
    'zone_created' => 'Zone created successfully',
    'zone_updated' => 'Zone updated successfully',
    'zone_deleted' => 'Zone deleted successfully',
    'zone_duplicated' => 'Zone duplicated successfully',
    
    // Validation
    'name_required' => 'Name is required',
    'slug_required' => 'URL slug is required',
    'code_required' => 'Code is required',
    'currency_required' => 'Currency is required',
    'slug_unique' => 'URL slug already exists',
    'code_unique' => 'Code already exists',
    
    // Filters
    'filter_enabled' => 'Filter by enabled status',
    'filter_default' => 'Filter by default status',
    'filter_currency' => 'Filter by currency',
    'filter_countries' => 'Filter by countries',
    
    // Statistics
    'total_zones' => 'Total Zones',
    'active_zones' => 'Active Zones',
    'default_zones' => 'Default Zones',
    'zones_with_countries' => 'Zones with Countries',
    'average_tax_rate' => 'Average Tax Rate',
    'total_shipping_cost' => 'Total Shipping Cost',
    
    // Frontend
    'select_zone' => 'Select Zone',
    'zone_not_found' => 'Zone not found',
    'shipping_to_zone' => 'Shipping to Zone',
    'tax_included' => 'Tax Included',
    'tax_excluded' => 'Tax Excluded',
    'free_shipping' => 'Free Shipping',
    'shipping_calculated' => 'Shipping calculated',
    
    // Widgets
    'zone_overview' => 'Zone Overview',
    'zone_statistics' => 'Zone Statistics',
    'recent_zones' => 'Recent Zones',
    'zone_performance' => 'Zone Performance',
    'zone_distribution' => 'Zone Distribution',
    
    // Export/Import
    'export_zones' => 'Export Zones',
    'import_zones' => 'Import Zones',
    'export_success' => 'Zones exported successfully',
    'import_success' => 'Zones imported successfully',
    'import_errors' => 'Import errors',
    
    // Bulk Actions
    'bulk_enable' => 'Enable Selected',
    'bulk_disable' => 'Disable Selected',
    'bulk_delete' => 'Delete Selected',
    'bulk_export' => 'Export Selected',
    
    // Search
    'search_zones' => 'Search zones...',
    'no_zones_found' => 'No zones found',
    'search_results' => 'Search Results',
    
    // Help
    'zone_help' => 'Zone Help',
    'zone_description_help' => 'Zone description helps identify its purpose',
    'tax_rate_help' => 'Tax rate in percentage (e.g., 21.00)',
    'shipping_rate_help' => 'Shipping cost in euros (e.g., 5.99)',
    'metadata_help' => 'Additional metadata in JSON format',
    
    // Additional fields
    'type' => 'Type',
    'type_shipping' => 'Shipping',
    'type_tax' => 'Tax',
    'type_payment' => 'Payment',
    'type_delivery' => 'Delivery',
    'type_general' => 'General',
    'priority' => 'Priority',
    'min_order_amount' => 'Min Order Amount',
    'max_order_amount' => 'Max Order Amount',
    'free_shipping_threshold' => 'Free Shipping Threshold',
    'is_active' => 'Active',
    'short_description' => 'Short Description',
    'long_description' => 'Long Description',
    'meta_title' => 'Meta Title',
    'meta_description' => 'Meta Description',
    'meta_keywords' => 'Meta Keywords',
    'meta_keywords_help' => 'Keywords separated by commas',
    
    // Help texts
    'is_enabled_help' => 'Whether the zone is enabled and can be used',
    'is_active_help' => 'Whether the zone is active in the system',
    'is_default_help' => 'Whether this is the default zone',
    'priority_help' => 'Zone priority (higher number = higher priority)',
    'min_order_amount_help' => 'Minimum order amount for this zone',
    'max_order_amount_help' => 'Maximum order amount for this zone',
    'free_shipping_threshold_help' => 'Order amount from which shipping is free',
    
    // Bulk actions
    'bulk_activate' => 'Activate Selected',
    'bulk_deactivate' => 'Deactivate Selected',
    
    // Filters
    'has_countries' => 'Has Countries',
    'free_shipping_available' => 'Free Shipping Available',
    
    // Widget translations
    'all_zones' => 'All zones in system',
    'available_zones' => 'Available zones',
    'enabled_zones_desc' => 'Enabled zones',
    'default_zones_desc' => 'Default zones',
    'shipping_zone_count' => 'Shipping zones',
    'tax_zone_count' => 'Tax zones',
    'payment_zone_count' => 'Payment zones',
    'delivery_zone_count' => 'Delivery zones',
    'general_zone_count' => 'General zones',
    'zones_with_countries_desc' => 'Zones with assigned countries',
    'zones_with_free_shipping_desc' => 'Zones with free shipping',
    'average_tax_rate_desc' => 'Average tax rate',
    'total_shipping_cost_desc' => 'Total shipping cost',
    'created_this_month' => 'Created this month',
    'zone_distribution' => 'Zone Distribution',
    'zone_type_distribution_desc' => 'Distribution of zones by type',
    'zone_count' => 'Zone Count',
    'recent_zones' => 'Recent Zones',
    'recent_zones_desc' => 'Recently created zones',
    
    // Frontend specific
    'zones_description' => 'View all our service zones',
    'view_details' => 'View Details',
    'no_zones_available' => 'No zones available at the moment',
    'shipping_calculator' => 'Shipping Calculator',
    'order_amount' => 'Order Amount',
    'weight' => 'Weight',
    'calculate_shipping' => 'Calculate Shipping',
    'calculation_results' => 'Calculation Results',
    'shipping_cost' => 'Shipping Cost',
    'tax_amount' => 'Tax Amount',
    'total_with_shipping' => 'Total with Shipping',
    'back_to_zones' => 'Back to Zones',
    'please_enter_order_amount' => 'Please enter order amount',
    'calculation_error' => 'Calculation error',
];
