<?php

return [
    'title' => 'City Management',
    'subtitle' => 'Manage cities and their translations',
    'navigation_label' => 'Cities',
    'navigation_group' => 'Content',
    'model_label' => 'City',
    'plural_model_label' => 'Cities',

    // Form fields
    'name' => 'Name',
    'slug' => 'URL Slug',
    'code' => 'Code',
    'description' => 'Description',
    'country' => 'Country',
    'zone' => 'Zone',
    'region' => 'Region',
    'parent_city' => 'Parent City',
    'level' => 'Level',
    'level_help' => '0 = City, 1 = District, 2 = Neighborhood, 3 = Suburb',
    'latitude' => 'Latitude',
    'longitude' => 'Longitude',
    'population' => 'Population',
    'postal_codes' => 'Postal Codes',
    'postal_codes_placeholder' => 'Enter postal code and press Enter',
    'is_enabled' => 'Enabled',
    'is_default' => 'Default',
    'is_capital' => 'Capital',
    'sort_order' => 'Sort Order',
    'metadata' => 'Metadata',
    'key' => 'Key',
    'value' => 'Value',

    // Translations section
    'translations' => 'Translations',
    'locale' => 'Locale',
    'add_translation' => 'Add Translation',

    // Table columns
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    // Actions
    'create' => 'Create City',
    'edit' => 'Edit City',
    'view' => 'View City',
    'delete' => 'Delete City',
    'save' => 'Save',
    'cancel' => 'Cancel',

    // Messages
    'created_successfully' => 'City created successfully',
    'updated_successfully' => 'City updated successfully',
    'deleted_successfully' => 'City deleted successfully',
    'no_cities_found' => 'No cities found',

    // Validation messages
    'name_required' => 'Name is required',
    'slug_required' => 'URL slug is required',
    'slug_unique' => 'This URL slug is already in use',
    'code_required' => 'Code is required',
    'code_unique' => 'This code is already in use',
    'country_required' => 'Country is required',

    // Level options
    'level_city' => 'City',
    'level_district' => 'District',
    'level_neighborhood' => 'Neighborhood',
    'level_suburb' => 'Suburb',

    // Status
    'status_enabled' => 'Enabled',
    'status_disabled' => 'Disabled',
    'status_default' => 'Default',
    'status_capital' => 'Capital',

    // Filters
    'filter_enabled' => 'Enabled cities',
    'filter_disabled' => 'Disabled cities',
    'filter_capital' => 'Capital cities',
    'filter_default' => 'Default cities',
    'filter_country' => 'Filter by country',
    'filter_region' => 'Filter by region',
    'filter_zone' => 'Filter by zone',
    'filter_level' => 'Filter by level',

    // Bulk actions
    'bulk_enable' => 'Enable selected',
    'bulk_disable' => 'Disable selected',
    'bulk_delete' => 'Delete selected',
    'bulk_actions' => 'Bulk actions',

    // Statistics
    'total_cities' => 'Total cities',
    'enabled_cities' => 'Enabled cities',
    'disabled_cities' => 'Disabled cities',
    'capital_cities' => 'Capital cities',
    'default_cities' => 'Default cities',

    // Locale options
    'locale_lt' => 'Lithuanian',
    'locale_en' => 'English',
    'locale_de' => 'German',
    'locale_ru' => 'Russian',
];
