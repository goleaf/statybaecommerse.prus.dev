<?php

return [
    'title' => 'Location Management',
    'subtitle' => 'Manage your warehouse and store locations',
    'navigation_label' => 'Locations',
    'navigation_group' => 'Inventory Management',
    
    // Form fields
    'code' => 'Code',
    'name' => 'Name',
    'address_line_1' => 'Address Line 1',
    'address_line_2' => 'Address Line 2',
    'city' => 'City',
    'state' => 'State/Province',
    'postal_code' => 'Postal Code',
    'country_code' => 'Country Code',
    'phone' => 'Phone',
    'email' => 'Email',
    'is_enabled' => 'Enabled',
    'is_default' => 'Default',
    'type' => 'Type',
    'description' => 'Description',
    
    // Table columns
    'address' => 'Address',
    'state' => 'State',
    'enabled' => 'Enabled',
    
    // Actions
    'create' => 'Create Location',
    'edit' => 'Edit Location',
    'view' => 'View Location',
    'delete' => 'Delete Location',
    'save' => 'Save',
    'cancel' => 'Cancel',
    
    // Messages
    'created_successfully' => 'Location created successfully',
    'updated_successfully' => 'Location updated successfully',
    'deleted_successfully' => 'Location deleted successfully',
    'no_locations_found' => 'No locations found',
    
    // Validation messages
    'code_required' => 'Code is required',
    'code_unique' => 'This code is already in use',
    'name_required' => 'Name is required',
    'email_invalid' => 'Invalid email address',
    'phone_invalid' => 'Invalid phone number',
    
    // Types
    'type_warehouse' => 'Warehouse',
    'type_store' => 'Store',
    'type_office' => 'Office',
    'type_pickup_point' => 'Pickup Point',
    'type_other' => 'Other',
    
    // Status
    'status_enabled' => 'Enabled',
    'status_disabled' => 'Disabled',
    'status_default' => 'Default',
    
    // Filters
    'filter_enabled' => 'Enabled locations',
    'filter_disabled' => 'Disabled locations',
    'filter_type' => 'Filter by type',
    'filter_country' => 'Filter by country',
    
    // Bulk actions
    'bulk_enable' => 'Enable selected',
    'bulk_disable' => 'Disable selected',
    'bulk_delete' => 'Delete selected',
    'bulk_actions' => 'Bulk actions',
    
    // Statistics
    'total_locations' => 'Total locations',
    'enabled_locations' => 'Enabled locations',
    'disabled_locations' => 'Disabled locations',
    'warehouse_count' => 'Warehouse count',
    'store_count' => 'Store count',
];
