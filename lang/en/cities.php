<?php

return [
    // Basic labels
    'plural' => 'Cities',
    'single' => 'City',
    'name' => 'Name',
    'code' => 'Code',
    'slug' => 'Slug',
    'description' => 'Description',
    'country' => 'Country',
    'city' => 'City',
    'state_province' => 'State/Province',
    'country_code' => 'Country Code',
    'postal_code' => 'Postal Code',

    // Coordinates
    'latitude' => 'Latitude',
    'longitude' => 'Longitude',
    'coordinates' => 'Coordinates',

    // Demographics
    'population' => 'Population',
    'area' => 'Area (km²)',
    'density' => 'Density (/km²)',
    'elevation' => 'Elevation (m)',
    'timezone' => 'Timezone',

    // Localization
    'currency_code' => 'Currency Code',
    'language_code' => 'Language Code',
    'phone_code' => 'Phone Code',

    // Hierarchy
    'parent_city' => 'Parent City',
    'level' => 'Level',

    // Settings
    'is_active' => 'Active',
    'is_capital' => 'Capital',
    'is_default' => 'Default',
    'sort_order' => 'Sort Order',
    'type' => 'Type',

    // Types
    'types' => [
        'metropolitan' => 'Metropolitan',
        'urban' => 'Urban',
        'rural' => 'Rural',
        'suburban' => 'Suburban',
        'industrial' => 'Industrial',
        'tourist' => 'Tourist',
    ],

    // Levels
    'levels' => [
        0 => 'City',
        1 => 'District',
        2 => 'Neighborhood',
        3 => 'Suburb',
        4 => 'Village',
        5 => 'Town',
    ],

    // Sections
    'basic_information' => 'Basic Information',
    'coordinates' => 'Coordinates',
    'demographics' => 'Demographics',
    'localization' => 'Localization',
    'hierarchy' => 'Hierarchy',
    'settings' => 'Settings',

    // Help text
    'slug_help' => 'URL-friendly version of the name',
    'code_help' => 'Short unique identifier',
    'latitude_help' => 'Latitude coordinate (-90 to 90)',
    'longitude_help' => 'Longitude coordinate (-180 to 180)',
    'population_help' => 'Number of inhabitants',
    'area_help' => 'Area in square kilometers',
    'density_help' => 'Population density per square kilometer',
    'elevation_help' => 'Elevation above sea level in meters',
    'timezone_help' => 'Timezone identifier (e.g., Europe/London)',
    'currency_code_help' => 'ISO 4217 currency code',
    'language_code_help' => 'ISO 639 language code',
    'phone_code_help' => 'International phone code',
    'parent_city_help' => 'Parent city for hierarchical structure',
    'level_help' => 'Hierarchy level (0-10)',
    'sort_order_help' => 'Display order (lower numbers first)',
    'type_help' => 'City classification type',

    // Actions
    'activate' => 'Activate',
    'deactivate' => 'Deactivate',
    'set_capital' => 'Set as Capital',
    'remove_capital' => 'Remove Capital',
    'set_default' => 'Set as Default',
    'remove_default' => 'Remove Default',

    // Bulk actions
    'activate_selected' => 'Activate Selected',
    'deactivate_selected' => 'Deactivate Selected',
    'set_capital_selected' => 'Set Capital Selected',
    'remove_capital_selected' => 'Remove Capital Selected',

    // Filters
    'active_only' => 'Active Only',
    'inactive_only' => 'Inactive Only',
    'capital_only' => 'Capital Only',
    'non_capital_only' => 'Non-Capital Only',
    'default_only' => 'Default Only',
    'non_default_only' => 'Non-Default Only',

    // Success messages
    'activated_successfully' => 'City activated successfully',
    'deactivated_successfully' => 'City deactivated successfully',
    'set_as_capital_success' => 'City set as capital successfully',
    'removed_from_capital_success' => 'City removed from capital successfully',
    'set_as_default_success' => 'City set as default successfully',
    'removed_from_default_success' => 'City removed from default successfully',

    // Bulk success messages
    'bulk_activated_success' => 'Selected cities activated successfully',
    'bulk_deactivated_success' => 'Selected cities deactivated successfully',
    'bulk_set_capital_success' => 'Selected cities set as capital successfully',
    'bulk_remove_capital_success' => 'Selected cities removed from capital successfully',

    // Timestamps
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    // Navigation
    'navigation_label' => 'Cities',
    'navigation_group' => 'Locations',
];
