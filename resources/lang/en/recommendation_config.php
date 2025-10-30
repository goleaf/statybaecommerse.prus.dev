<?php

declare(strict_types=1);

return [
    // Section labels keep the admin form organised and mirror the Livewire tests.
    'sections' => [
        'basic_info'    => 'Basic information',
        'parameters'    => 'Parameters',
        'flags'         => 'Flags',
        'relationships' => 'Relationships',
    ],
    // Field labels surfaced throughout the Filament resource.
    'fields' => [
        'name'        => 'Name',
        'type'        => 'Type',
        'description' => 'Description',
        'min_score'   => 'Minimum score',
        'max_results' => 'Maximum results',
        'decay_factor'=> 'Decay factor',
        'priority'    => 'Priority',
        'cache_ttl'   => 'Cache TTL',
        'sort_order'  => 'Sort order',
        'is_active'   => 'Is active',
        'is_default'  => 'Is default',
        'products'    => 'Products',
        'categories'  => 'Categories',
        'created_at'  => 'Created at',
    ],
    // Action labels reused by page header actions and bulk table tools.
    'actions' => [
        'toggle_active'       => 'Toggle active state',
        'set_default'         => 'Set as default',
        'activate_selected'   => 'Activate selected',
        'deactivate_selected' => 'Deactivate selected',
    ],
];
