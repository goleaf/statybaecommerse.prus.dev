<?php

return [
    'title' => 'System Settings',
    'single' => 'System Setting',
    'plural' => 'System Settings',
    
    'navigation' => [
        'title' => 'System Settings',
        'description' => 'Manage system configuration and settings',
    ],
    
    'form' => [
        'basic_settings' => 'Basic Settings',
        'advanced_settings' => 'Advanced Settings',
        'dependencies' => 'Dependencies & Relations',
        'translations' => 'Translations',
        
        'system_information' => 'System Information',
        'value_configuration' => 'Value Configuration',
        'validation_constraints' => 'Validation & Constraints',
        'access_control' => 'Access Control',
        'system_integration' => 'System Integration',
        'dependencies' => 'Dependencies',
        'relations' => 'Relations',
        'multi_language_support' => 'Multi-language Support',
        
        'fields' => [
            'key' => 'Setting Key',
            'name' => 'Display Name',
            'description' => 'Description',
            'type' => 'Setting Type',
            'value' => 'Setting Value',
            'category' => 'Category',
            'validation_rules' => 'Validation Rules',
            'default_value' => 'Default Value',
            'is_required' => 'Required Setting',
            'is_encrypted' => 'Encrypt Value',
            'permission_required' => 'Required Permission',
            'user_id' => 'Created By',
            'cache_key' => 'Cache Key',
            'cache_ttl' => 'Cache TTL (seconds)',
            'is_public' => 'Public Setting',
            'is_readonly' => 'Read Only',
        ],
        
        'types' => [
            'string' => 'Text',
            'integer' => 'Number',
            'boolean' => 'Yes/No',
            'json' => 'JSON Data',
            'array' => 'Array',
            'file' => 'File Upload',
            'color' => 'Color',
            'date' => 'Date',
            'datetime' => 'Date & Time',
            'email' => 'Email',
            'url' => 'URL',
            'password' => 'Password',
        ],
        
        'permissions' => [
            'admin' => 'Admin Only',
            'manager' => 'Manager+',
            'user' => 'Any User',
            'system' => 'System Only',
        ],
        
        'cache_ttl_options' => [
            0 => 'No Cache',
            60 => '1 Minute',
            300 => '5 Minutes',
            900 => '15 Minutes',
            3600 => '1 Hour',
            86400 => '1 Day',
        ],
    ],
    
    'table' => [
        'columns' => [
            'key' => 'Setting Key',
            'name' => 'Display Name',
            'category' => 'Category',
            'type' => 'Type',
            'value' => 'Value',
            'required' => 'Required',
            'public' => 'Public',
            'readonly' => 'Read Only',
            'created_by' => 'Created By',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
        ],
        
        'actions' => [
            'clear_cache' => 'Clear Cache',
            'export' => 'Export',
            'system_health' => 'System Health',
            'optimize_system' => 'Optimize System',
            'clear_all_caches' => 'Clear All Caches',
        ],
        
        'bulk_actions' => [
            'clear_all_cache' => 'Clear All Cache',
            'export_selected' => 'Export Selected',
        ],
    ],
    
    'tabs' => [
        'all' => 'All Settings',
        'general' => 'General',
        'security' => 'Security',
        'performance' => 'Performance',
        'ui_ux' => 'UI/UX',
        'api' => 'API',
        'required' => 'Required',
        'public' => 'Public',
        'readonly' => 'Read Only',
    ],
    
    'notifications' => [
        'created' => 'System setting created successfully',
        'updated' => 'System setting updated successfully',
        'deleted' => 'System setting deleted successfully',
        'cache_cleared' => 'Cache cleared successfully',
        'value_refreshed' => 'Value refreshed successfully',
        'system_optimized' => 'System optimized successfully',
        'all_caches_cleared' => 'All caches cleared successfully',
    ],
    
    'widgets' => [
        'stats' => [
            'total_settings' => 'Total Settings',
            'categories' => 'Categories',
            'required_settings' => 'Required Settings',
            'public_settings' => 'Public Settings',
            'readonly_settings' => 'Read Only Settings',
            'encrypted_settings' => 'Encrypted Settings',
            'cache_hit_rate' => 'Cache Hit Rate',
        ],
    ],
];
