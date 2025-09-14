<?php

return [
    'navigation_label' => 'Regions',
    'model_label' => 'Region',
    'plural_model_label' => 'Regions',
    
    // Page titles
    'title' => 'Regions',
    'subtitle' => 'Explore regions and administrative divisions',
    'page_title' => 'Regions Directory',
    'page_description' => 'Browse through all available regions and administrative divisions',
    
    // Fields
    'fields' => [
        'name' => 'Name',
        'name_official' => 'Official Name',
        'code' => 'Code',
        'description' => 'Description',
        'is_enabled' => 'Enabled',
        'is_default' => 'Default',
        'country' => 'Country',
        'zone' => 'Zone',
        'parent' => 'Parent Region',
        'level' => 'Level',
        'sort_order' => 'Sort Order',
        'metadata' => 'Metadata',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
        'yes' => 'Yes',
        'no' => 'No',
        'capital' => 'Capital',
    ],
    
    // Form sections
    'basic_information' => 'Basic Information',
    'hierarchy_settings' => 'Hierarchy Settings',
    'status_settings' => 'Status Settings',
    'additional_data' => 'Additional Data',
    
    // Placeholders
    'placeholders' => [
        'name' => 'Enter region name',
        'code' => 'Enter region code',
        'description' => 'Enter region description',
        'sort_order' => 'Enter sort order',
    ],
    
    // Help text
    'help' => [
        'name' => 'The name of the region',
        'code' => 'A unique code for the region',
        'description' => 'A brief description of the region',
        'parent' => 'Select a parent region if this is a sub-region',
        'level' => 'The administrative level of this region',
        'sort_order' => 'The order in which this region should appear',
    ],
    
    // Actions
    'actions' => [
        'create' => 'Create Region',
        'edit' => 'Edit Region',
        'delete' => 'Delete Region',
        'view' => 'View Region',
        'back_to_list' => 'Back to Regions',
        'view_details' => 'View Details',
        'show_on_map' => 'Show on Map',
    ],
    
    // Filters
    'filters' => [
        'search' => 'Search',
        'search_placeholder' => 'Search regions...',
        'by_country' => 'Filter by Country',
        'by_zone' => 'Filter by Zone',
        'by_level' => 'Filter by Level',
        'by_parent' => 'Filter by Parent',
        'all_countries' => 'All Countries',
        'all_zones' => 'All Zones',
        'all_levels' => 'All Levels',
        'all_parents' => 'All Parent Regions',
        'enabled_only' => 'Enabled Only',
        'disabled_only' => 'Disabled Only',
        'default_only' => 'Default Only',
        'non_default_only' => 'Non-Default Only',
        'has_children' => 'Has Children',
        'has_cities' => 'Has Cities',
        'root_regions' => 'Root Regions',
        'leaf_regions' => 'Leaf Regions',
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
    ],
    
    // Levels
    'levels' => [
        0 => 'Root',
        1 => 'State/Province',
        2 => 'County',
        3 => 'District',
        4 => 'Municipality',
        5 => 'Village',
    ],
    
    // Statistics
    'statistics' => [
        'total_regions' => 'Total Regions',
        'total_regions_description' => 'Total number of regions in the system',
        'enabled_regions' => 'Enabled Regions',
        'enabled_regions_description' => 'Number of enabled regions',
        'default_regions' => 'Default Regions',
        'default_regions_description' => 'Number of default regions',
        'root_regions' => 'Root Regions',
        'root_regions_description' => 'Number of root level regions',
        'regions_by_country' => 'Regions by Country',
        'regions_by_level' => 'Regions by Level',
        'recent_regions' => 'Recent Regions',
    ],
    
    // Widgets
    'widgets' => [
        'overview' => 'Regions Overview',
        'by_country_chart' => 'Regions by Country',
        'by_level_chart' => 'Regions by Level',
        'recent_table' => 'Recent Regions',
        'hierarchy_tree' => 'Region Hierarchy',
        'geographic_distribution' => 'Geographic Distribution',
    ],
    
    // Messages
    'messages' => [
        'created' => 'Region created successfully',
        'updated' => 'Region updated successfully',
        'deleted' => 'Region deleted successfully',
        'restored' => 'Region restored successfully',
        'no_regions_found' => 'No regions found',
        'try_different_filters' => 'Try adjusting your search filters',
        'region_not_found' => 'Region not found',
        'cannot_delete_with_children' => 'Cannot delete region with child regions',
        'cannot_delete_with_cities' => 'Cannot delete region with cities',
    ],
    
    // Confirmations
    'confirmations' => [
        'delete' => 'Are you sure you want to delete this region?',
        'delete_with_children' => 'This region has child regions. Are you sure you want to delete it?',
        'delete_with_cities' => 'This region has cities. Are you sure you want to delete it?',
        'bulk_delete' => 'Are you sure you want to delete the selected regions?',
    ],
    
    // Empty states
    'empty_states' => [
        'no_regions' => 'No regions found',
        'no_regions_description' => 'Get started by creating your first region',
        'no_regions_found' => 'No regions match your search criteria',
        'no_regions_found_description' => 'Try adjusting your search or filters',
    ],
    
    // Validation
    'validation' => [
        'name_required' => 'Region name is required',
        'name_unique' => 'Region name must be unique',
        'code_unique' => 'Region code must be unique',
        'parent_exists' => 'Selected parent region does not exist',
        'country_required' => 'Country is required',
        'level_invalid' => 'Invalid region level',
    ],
    
    // Details sections
    'details' => [
        'basic_info' => 'Basic Information',
        'hierarchy_info' => 'Hierarchy Information',
        'geographic_info' => 'Geographic Information',
        'business_info' => 'Business Information',
        'related_cities' => 'Related Cities',
        'child_regions' => 'Child Regions',
        'contact_info' => 'Contact Information',
        'actions' => 'Actions',
        'major_cities' => 'Major Cities',
        'and_more' => 'and :count more',
    ],
    
    // API responses
    'api' => [
        'success' => 'Success',
        'error' => 'Error',
        'not_found' => 'Region not found',
        'validation_failed' => 'Validation failed',
    ],
];