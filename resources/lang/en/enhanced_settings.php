<?php declare(strict_types=1);

return [
    // Navigation and Labels
    'enhanced_settings' => 'Enhanced Settings',
    'enhanced_setting' => 'Enhanced Setting',
    'settings' => 'Settings',
    'setting' => 'Setting',
    // Form Fields
    'group' => 'Group',
    'key' => 'Key',
    'value' => 'Value',
    'type' => 'Type',
    'description' => 'Description',
    'is_public' => 'Is Public',
    'is_encrypted' => 'Is Encrypted',
    'validation_rules' => 'Validation Rules',
    'sort_order' => 'Sort Order',
    // Groups
    'groups' => [
        'general' => 'General',
        'ecommerce' => 'E-commerce',
        'email' => 'Email',
        'payment' => 'Payment',
        'shipping' => 'Shipping',
        'seo' => 'SEO',
        'security' => 'Security',
        'api' => 'API',
        'appearance' => 'Appearance',
        'notifications' => 'Notifications',
    ],
    // Types
    'types' => [
        'text' => 'Text',
        'textarea' => 'Textarea',
        'number' => 'Number',
        'boolean' => 'Boolean',
        'json' => 'JSON',
        'array' => 'Array',
        'select' => 'Select',
        'file' => 'File',
        'color' => 'Color',
        'date' => 'Date',
        'datetime' => 'DateTime',
    ],
    // Form Sections
    'setting_information' => 'Setting Information',
    'value_configuration' => 'Value Configuration',
    'advanced_options' => 'Advanced Options',
    // Help Text
    'help' => [
        'key' => 'Use lowercase letters, numbers, underscores and dots only',
        'type' => 'Data type for this setting',
        'is_public' => 'Public settings can be accessed from frontend',
        'is_encrypted' => 'Sensitive settings will be encrypted in database',
        'validation_rules' => 'Laravel validation rules in key-value format',
        'json_format' => 'Enter valid JSON format',
        'group_description' => 'Group this setting belongs to (e.g., "general", "email", "payment")',
    ],
    // Field Labels
    'labels' => [
        'json_value' => 'JSON Value',
        'color_value' => 'Color Value',
        'date_value' => 'Date Value',
        'datetime_value' => 'DateTime Value',
        'public' => 'Public',
        'encrypted' => 'Encrypted',
        'last_updated' => 'Last Updated',
        'updated_at' => 'Updated At',
    ],
    // Actions
    'actions' => [
        'create' => 'Create Setting',
        'edit' => 'Edit Setting',
        'delete' => 'Delete Setting',
        'view' => 'View Setting',
        'save' => 'Save Setting',
        'cancel' => 'Cancel',
        'back' => 'Back to Settings',
    ],
    // Messages
    'messages' => [
        'created' => 'Setting created successfully',
        'updated' => 'Setting updated successfully',
        'deleted' => 'Setting deleted successfully',
        'not_found' => 'Setting not found',
        'validation_failed' => 'Validation failed',
        'key_exists' => 'A setting with this key already exists',
        'invalid_json' => 'Invalid JSON format',
        'encryption_failed' => 'Failed to encrypt setting value',
        'decryption_failed' => 'Failed to decrypt setting value',
    ],
    // Filters
    'filters' => [
        'all_groups' => 'All Groups',
        'all_types' => 'All Types',
        'public_only' => 'Public Only',
        'private_only' => 'Private Only',
        'encrypted_only' => 'Encrypted Only',
        'non_encrypted' => 'Non-encrypted',
    ],
    // Table Headers
    'table' => [
        'group' => 'Group',
        'key' => 'Key',
        'type' => 'Type',
        'value' => 'Value',
        'public' => 'Public',
        'encrypted' => 'Encrypted',
        'updated_at' => 'Updated At',
        'actions' => 'Actions',
    ],
    // Validation Messages
    'validation' => [
        'key_required' => 'Setting key is required',
        'key_unique' => 'Setting key must be unique',
        'key_format' => 'Setting key must contain only lowercase letters, numbers, underscores and dots',
        'value_required' => 'Setting value is required',
        'type_required' => 'Setting type is required',
        'group_required' => 'Setting group is required',
        'sort_order_numeric' => 'Sort order must be a number',
        'json_valid' => 'Value must be valid JSON format',
    ],
    // Tooltips
    'tooltips' => [
        'public_setting' => 'This setting can be accessed from the frontend',
        'encrypted_setting' => 'This setting value is encrypted in the database',
        'json_setting' => 'This setting stores JSON data',
        'required_setting' => 'This setting is required for system operation',
        'copy_key' => 'Click to copy setting key',
        'edit_setting' => 'Edit this setting',
        'delete_setting' => 'Delete this setting',
        'view_setting' => 'View setting details',
    ],
    // Placeholders
    'placeholders' => [
        'key' => 'e.g., site_name, app_version',
        'value' => 'Enter setting value',
        'description' => 'Describe what this setting controls',
        'json_value' => '{"key": "value", "array": [1, 2, 3]}',
        'validation_rules' => 'required|string|max:255',
        'sort_order' => '0',
    ],
    // Empty States
    'empty_states' => [
        'no_settings' => 'No settings found',
        'no_settings_description' => 'Create your first setting to get started',
        'no_results' => 'No settings match your search criteria',
        'no_results_description' => 'Try adjusting your filters or search terms',
    ],
    // Bulk Actions
    'bulk_actions' => [
        'delete_selected' => 'Delete Selected',
        'export_selected' => 'Export Selected',
        'make_public' => 'Make Public',
        'make_private' => 'Make Private',
        'encrypt_selected' => 'Encrypt Selected',
        'decrypt_selected' => 'Decrypt Selected',
    ],
    // Import/Export
    'import_export' => [
        'import' => 'Import Settings',
        'export' => 'Export Settings',
        'import_success' => 'Settings imported successfully',
        'export_success' => 'Settings exported successfully',
        'import_failed' => 'Failed to import settings',
        'export_failed' => 'Failed to export settings',
        'invalid_file' => 'Invalid file format',
        'file_too_large' => 'File is too large',
    ],
];
