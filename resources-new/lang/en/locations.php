<?php

return [
    'navigation_label' => 'Locations',
    'model_label' => 'Location',
    'plural_model_label' => 'Locations',

    // Page titles
    'title' => 'Locations',
    'subtitle' => 'Find our stores, warehouses, and pickup points',
    'page_title' => 'Locations Directory',
    'page_description' => 'Browse through all our locations including stores, warehouses, and pickup points',

    // Fields
    'fields' => [
        'name' => 'Name',
        'description' => 'Description',
        'code' => 'Code',
        'slug' => 'Slug',
        'type' => 'Type',
        'address_line_1' => 'Address Line 1',
        'address_line_2' => 'Address Line 2',
        'city' => 'City',
        'state' => 'State',
        'postal_code' => 'Postal Code',
        'country_code' => 'Country Code',
        'phone' => 'Phone',
        'email' => 'Email',
        'is_enabled' => 'Enabled',
        'is_default' => 'Default',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'opening_hours' => 'Opening Hours',
        'contact_info' => 'Contact Info',
        'sort_order' => 'Sort Order',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
        'yes' => 'Yes',
        'no' => 'No',
        'coordinates' => 'Coordinates',
        'full_address' => 'Full Address',
    ],

    // Form sections
    'basic_information' => 'Basic Information',
    'address_information' => 'Address Information',
    'contact_information' => 'Contact Information',
    'location_details' => 'Location Details',
    'business_settings' => 'Business Settings',
    'additional_data' => 'Additional Data',

    // Placeholders
    'placeholders' => [
        'name' => 'Enter location name',
        'code' => 'Enter location code',
        'description' => 'Enter location description',
        'slug' => 'Enter location slug',
        'address_line_1' => 'Enter street address',
        'city' => 'Enter city',
        'state' => 'Enter state/province',
        'postal_code' => 'Enter postal code',
        'phone' => 'Enter phone number',
        'email' => 'Enter email address',
        'latitude' => 'Enter latitude',
        'longitude' => 'Enter longitude',
        'sort_order' => 'Enter sort order',
    ],

    // Help text
    'help' => [
        'name' => 'The name of the location',
        'code' => 'A unique code for the location',
        'description' => 'A brief description of the location',
        'type' => 'The type of location (store, warehouse, office, etc.)',
        'address_line_1' => 'Primary street address',
        'city' => 'City where the location is situated',
        'phone' => 'Contact phone number',
        'email' => 'Contact email address',
        'latitude' => 'Geographic latitude coordinate',
        'longitude' => 'Geographic longitude coordinate',
        'opening_hours' => 'Business opening hours for each day',
        'sort_order' => 'The order in which this location should appear',
    ],

    // Location types
    'type_warehouse' => 'Warehouse',
    'type_store' => 'Store',
    'type_office' => 'Office',
    'type_pickup_point' => 'Pickup Point',
    'type_other' => 'Other',

    // Actions
    'actions' => [
        'create' => 'Create Location',
        'edit' => 'Edit Location',
        'delete' => 'Delete Location',
        'view' => 'View Location',
        'back_to_list' => 'Back to Locations',
        'view_details' => 'View Details',
        'show_on_map' => 'Show on Map',
        'get_directions' => 'Get Directions',
        'contact_location' => 'Contact Location',
    ],

    // Filters
    'filters' => [
        'search' => 'Search',
        'search_placeholder' => 'Search locations...',
        'by_type' => 'Filter by Type',
        'by_country' => 'Filter by Country',
        'by_city' => 'Filter by City',
        'all_types' => 'All Types',
        'all_countries' => 'All Countries',
        'all_cities' => 'All Cities',
        'enabled_only' => 'Enabled Only',
        'disabled_only' => 'Disabled Only',
        'default_only' => 'Default Only',
        'non_default_only' => 'Non-Default Only',
        'has_coordinates' => 'Has Coordinates',
        'has_opening_hours' => 'Has Opening Hours',
        'is_open_now' => 'Open Now',
        'apply_filters' => 'Apply Filters',
        'clear_filters' => 'Clear Filters',
    ],

    // Status
    'status' => [
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'default' => 'Default',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'open' => 'Open',
        'closed' => 'Closed',
    ],

    // Days of the week
    'monday' => 'Monday',
    'tuesday' => 'Tuesday',
    'wednesday' => 'Wednesday',
    'thursday' => 'Thursday',
    'friday' => 'Friday',
    'saturday' => 'Saturday',
    'sunday' => 'Sunday',

    // Statistics
    'statistics' => [
        'total_locations' => 'Total Locations',
        'total_locations_description' => 'Total number of locations in the system',
        'enabled_locations' => 'Enabled Locations',
        'enabled_locations_description' => 'Number of enabled locations',
        'disabled_locations' => 'Disabled Locations',
        'disabled_locations_description' => 'Number of disabled locations',
        'warehouse_count' => 'Warehouses',
        'warehouse_count_description' => 'Number of warehouse locations',
        'store_count' => 'Stores',
        'store_count_description' => 'Number of store locations',
        'office_count' => 'Offices',
        'office_count_description' => 'Number of office locations',
        'pickup_point_count' => 'Pickup Points',
        'pickup_point_count_description' => 'Number of pickup point locations',
        'locations_by_type' => 'Locations by Type',
        'locations_count' => 'Locations Count',
        'total_inventory_value' => 'Total Inventory Value',
        'total_inventory_value_description' => 'Total value of inventory across all locations',
        'total_products' => 'Total Products',
        'total_products_description' => 'Total number of products in inventory',
        'low_stock_products' => 'Low Stock Products',
        'low_stock_products_description' => 'Number of products with low stock',
        'out_of_stock_products' => 'Out of Stock Products',
        'out_of_stock_products_description' => 'Number of products out of stock',
    ],

    // Widgets
    'widgets' => [
        'overview' => 'Locations Overview',
        'by_type_chart' => 'Locations by Type',
        'inventory_overview' => 'Inventory Overview',
        'recent_locations' => 'Recent Locations',
        'geographic_distribution' => 'Geographic Distribution',
        'opening_hours_summary' => 'Opening Hours Summary',
    ],

    // Messages
    'messages' => [
        'created' => 'Location created successfully',
        'updated' => 'Location updated successfully',
        'deleted' => 'Location deleted successfully',
        'restored' => 'Location restored successfully',
        'no_locations_found' => 'No locations found',
        'try_different_filters' => 'Try adjusting your search filters',
        'location_not_found' => 'Location not found',
        'cannot_delete_with_inventory' => 'Cannot delete location with inventory',
        'opening_hours_updated' => 'Opening hours updated successfully',
    ],

    // Confirmations
    'confirmations' => [
        'delete' => 'Are you sure you want to delete this location?',
        'delete_with_inventory' => 'This location has inventory. Are you sure you want to delete it?',
        'bulk_delete' => 'Are you sure you want to delete the selected locations?',
        'bulk_enable' => 'Are you sure you want to enable the selected locations?',
        'bulk_disable' => 'Are you sure you want to disable the selected locations?',
    ],

    // Empty states
    'empty_states' => [
        'no_locations' => 'No locations found',
        'no_locations_description' => 'Get started by creating your first location',
        'no_locations_found' => 'No locations match your search criteria',
        'no_locations_found_description' => 'Try adjusting your search or filters',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Location name is required',
        'name_unique' => 'Location name must be unique',
        'code_required' => 'Location code is required',
        'code_unique' => 'Location code must be unique',
        'type_required' => 'Location type is required',
        'type_invalid' => 'Invalid location type',
        'latitude_numeric' => 'Latitude must be a number',
        'longitude_numeric' => 'Longitude must be a number',
        'phone_format' => 'Invalid phone number format',
        'email_format' => 'Invalid email format',
    ],

    // Details sections
    'details' => [
        'basic_info' => 'Basic Information',
        'address_info' => 'Address Information',
        'contact_info' => 'Contact Information',
        'business_info' => 'Business Information',
        'opening_hours_info' => 'Opening Hours',
        'inventory_info' => 'Inventory Information',
        'actions' => 'Actions',
        'related_locations' => 'Related Locations',
        'nearby_locations' => 'Nearby Locations',
    ],

    // Bulk actions
    'bulk_enable' => 'Enable Selected',
    'bulk_disable' => 'Disable Selected',
    'bulk_delete' => 'Delete Selected',

    // API responses
    'api' => [
        'success' => 'Success',
        'error' => 'Error',
        'not_found' => 'Location not found',
        'validation_failed' => 'Validation failed',
        'inventory_updated' => 'Inventory updated successfully',
    ],
];
