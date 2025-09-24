<?php

declare(strict_types=1);

return [
    'navigation_label' => 'User Preferences',
    'plural_model_label' => 'User Preferences',
    'model_label' => 'User Preference',

    // Form fields
    'user' => 'User',
    'preference_type' => 'Preference Type',
    'preference_key' => 'Preference Key',
    'preference_score' => 'Preference Score',
    'last_updated' => 'Last Updated',
    'metadata' => 'Metadata',
    'key' => 'Key',
    'value' => 'Value',

    // Table columns
    'created_at' => 'Created At',

    // Filters
    'min_score' => 'Minimum Score',
    'max_score' => 'Maximum Score',

    // Actions
    'reset_preference' => 'Reset Preference',
    'reset_preferences' => 'Reset Preferences',

    // Notifications
    'preference_reset_successfully' => 'Preference has been reset successfully.',
    'preferences_reset_successfully' => 'Selected preferences have been reset successfully.',

    // Preference types
    'preference_types' => [
        'category' => 'Category',
        'brand' => 'Brand',
        'price_range' => 'Price Range',
        'color' => 'Color',
        'size' => 'Size',
        'material' => 'Material',
        'style' => 'Style',
        'feature' => 'Feature',
    ],

    // Validation messages
    'validation' => [
        'user_id_required' => 'User is required.',
        'preference_type_required' => 'Preference type is required.',
        'preference_score_numeric' => 'Preference score must be a number.',
        'preference_score_min' => 'Preference score must be at least 0.',
        'preference_score_max' => 'Preference score must not exceed 1.',
    ],

    // Help text
    'help' => [
        'preference_score' => 'Score between 0 and 1 indicating preference strength.',
        'metadata' => 'Additional data associated with this preference.',
        'last_updated' => 'When this preference was last updated.',
    ],

    // Breadcrumbs
    'breadcrumbs' => [
        'index' => 'User Preferences',
        'create' => 'Create User Preference',
        'edit' => 'Edit User Preference',
        'view' => 'View User Preference',
    ],
];
