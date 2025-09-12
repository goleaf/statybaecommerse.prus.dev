<?php

return [
    /*
     * |--------------------------------------------------------------------------
     * | Code Style Configuration
     * |--------------------------------------------------------------------------
     * |
     * | This file contains configuration options for the code style service.
     * | You can customize import order, validation rules, and other settings.
     * |
     */
    'import_order' => [
        'Illuminate\\',
        'Filament\\',
        'Spatie\\',
        'App\\',
    ],
    'validation_rules' => [
        'import_order' => true,
        'union_type_spacing' => true,
        'closure_spacing' => true,
        'trailing_whitespace' => true,
        'numeric_formatting' => true,
        'final_newline' => true,
        'indentation' => true,
    ],
    'fixing_rules' => [
        'import_order' => true,
        'union_type_spacing' => true,
        'closure_spacing' => true,
        'trailing_whitespace' => true,
        'numeric_formatting' => true,
        'final_newline' => true,
        'indentation' => true,
    ],
    'patterns' => [
        'union_type' => '/\|\s*([a-zA-Z\\\\]+)/',
        'closure' => '/fn\s*\(\s*([^)]*)\s*\)/',
        'trailing_whitespace' => '/\s+$/m',
        'numeric' => '/(\d+\.0+)(?![0-9])/',
        'import' => '/^use\s+/',
    ],
    'directories' => [
        'include' => [
            'app/',
            'tests/',
            'database/',
        ],
        'exclude' => [
            'vendor/',
            'node_modules/',
            'storage/',
            'bootstrap/cache/',
        ],
    ],
    'extensions' => [
        'php',
    ],
    'reporting' => [
        'enabled' => true,
        'path' => storage_path('logs/code-style-reports'),
        'format' => 'json',  // json, csv, html
    ],
    'auto_fix' => [
        'enabled' => env('CODE_STYLE_AUTO_FIX', false),
        'on_save' => env('CODE_STYLE_AUTO_FIX_ON_SAVE', false),
        'on_upload' => env('CODE_STYLE_AUTO_FIX_ON_UPLOAD', false),
    ],
    'watch' => [
        'enabled' => env('CODE_STYLE_WATCH', false),
        'interval' => 1,  // seconds
        'paths' => [
            'app/',
            'tests/',
        ],
    ],
];
