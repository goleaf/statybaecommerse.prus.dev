<?php

return [
    // Navigation
    'navigation_label' => 'Cities',
    'navigation_group' => 'Content',
    'model_label' => 'City',
    'plural_model_label' => 'Cities',

    // Basic Information
    'basic_information' => 'Basic Information',
    'name' => 'Name',
    'slug' => 'URL Slug',
    'code' => 'Code',
    'description' => 'Description',

    // Location
    'location' => 'Location',
    'country' => 'Country',
    'zone' => 'Zone',
    'region' => 'Region',
    'parent_city' => 'Parent City',
    'level' => 'Level',
    'level_city' => 'City',
    'level_district' => 'District',
    'level_neighborhood' => 'Neighborhood',
    'level_suburb' => 'Suburb',
    'level_help' => 'Hierarchy level: 0=city, 1=district, 2=neighborhood, 3=suburb',

    // Geographic Data
    'geographic_data' => 'Geographic Data',
    'latitude' => 'Latitude',
    'longitude' => 'Longitude',
    'population' => 'Population',
    'postal_codes' => 'Postal Codes',
    'postal_codes_placeholder' => 'Enter postal codes',

    // Status
    'status' => 'Status',
    'is_enabled' => 'Enabled',
    'is_default' => 'Default',
    'is_capital' => 'Capital',
    'is_active' => 'Active',
    'sort_order' => 'Sort Order',

    // Translations
    'translations' => 'Translations',
    'locale' => 'Locale',
    'locale_lt' => 'Lithuanian',
    'locale_en' => 'English',
    'locale_de' => 'German',
    'locale_ru' => 'Russian',
    'add_translation' => 'Add Translation',

    // Metadata
    'metadata' => 'Metadata',
    'key' => 'Key',
    'value' => 'Value',

    // Actions
    'view' => 'View',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'bulk_delete' => 'Delete Selected',
    'create' => 'Create',
    'save' => 'Save',
    'cancel' => 'Cancel',

    // Filters
    'filter_all' => 'All',
    'filter_enabled' => 'Enabled',
    'filter_disabled' => 'Disabled',
    'filter_capital' => 'Capitals',
    'filter_non_capital' => 'Non-capitals',
    'filter_default' => 'Default',
    'filter_non_default' => 'Non-default',
    'with_coordinates' => 'With Coordinates',
    'with_population' => 'With Population',
    'population_from' => 'Population From',
    'population_to' => 'Population To',

    // Messages
    'created_successfully' => 'City created successfully',
    'updated_successfully' => 'City updated successfully',
    'deleted_successfully' => 'City deleted successfully',
    'bulk_deleted_successfully' => 'Selected cities deleted successfully',
    'restored_successfully' => 'City restored successfully',
    'force_deleted_successfully' => 'City permanently deleted',

    // Validation
    'validation_name_required' => 'Name is required',
    'validation_name_max' => 'Name cannot be longer than 255 characters',
    'validation_slug_required' => 'URL slug is required',
    'validation_slug_unique' => 'URL slug already exists',
    'validation_code_required' => 'Code is required',
    'validation_code_unique' => 'Code already exists',
    'validation_country_required' => 'Country is required',

    // Statistics
    'total_cities' => 'Total Cities',
    'enabled_cities' => 'Enabled Cities',
    'capital_cities' => 'Capital Cities',
    'cities_with_population' => 'Cities with Population',
    'cities_with_coordinates' => 'Cities with Coordinates',

    // Frontend
    'select_city' => 'Select City',
    'search_cities' => 'Search cities...',
    'no_cities_found' => 'No cities found',
    'city_details' => 'City Details',
    'related_cities' => 'Related Cities',
    'nearby_cities' => 'Nearby Cities',

    // Additional fields
    'type' => 'Type',
    'area' => 'Area',
    'density' => 'Density',
    'elevation' => 'Elevation',
    'timezone' => 'Timezone',
    'currency_code' => 'Currency Code',
    'currency_symbol' => 'Currency Symbol',
    'language_code' => 'Language Code',
    'language_name' => 'Language Name',
    'phone_code' => 'Phone Code',
    'postal_code' => 'Postal Code',

    // Timestamps
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'deleted_at' => 'Deleted At',

    // Export/Import
    'export' => 'Export',
    'import' => 'Import',
    'export_cities' => 'Export Cities',
    'import_cities' => 'Import Cities',

    // Bulk Actions
    'bulk_actions' => 'Bulk Actions',
    'bulk_enable' => 'Enable Selected',
    'bulk_disable' => 'Disable Selected',
    'bulk_set_as_capital' => 'Set as Capital',
    'bulk_remove_capital' => 'Remove from Capitals',
];
