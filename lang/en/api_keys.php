<?php

declare(strict_types=1);

return [
    'navigation' => [
        'label'    => 'API Keys',
        'singular' => 'API Key',
        'plural'   => 'API Keys',
    ],
    'sections' => [
        'details'     => 'Details',
        'credentials' => 'Credentials',
        'activity'    => 'Activity',
    ],
    'fields' => [
        'name'           => 'Name',
        'scopes'         => 'Scopes',
        'rate_limit'     => 'Rate limit (requests / minute)',
        'active'         => 'Active',
        'plain_text_key' => 'Secret key',
        'last_used_at'   => 'Last used',
        'created_at'     => 'Created at',
        'updated_at'     => 'Updated at',
    ],
    'placeholders' => [
        'name'       => 'Internal name used for auditing',
        'rate_limit' => 'Unlimited',
    ],
    'hints' => [
        'scopes'         => 'Select the permissions that this key will grant.',
        'rate_limit'     => 'Set the number of allowed requests per minute. Leave empty for unlimited access.',
        'generated_once' => 'Copy the secret now. It will not be shown again after you leave this page.',
    ],
    'filters' => [
        'active' => 'Status',
        'scope'  => 'Scope',
    ],
    'actions' => [
        'create'             => 'Create API Key',
        'regenerate'         => 'Regenerate',
        'confirm_regenerate' => 'Regenerate key',
        'reveal'             => 'Reveal',
        'hide'               => 'Hide',
        'copy'               => 'Copy',
        'close'              => 'Close',
    ],
    'notifications' => [
        'created'     => 'API key created successfully.',
        'updated'     => 'API key updated successfully.',
        'regenerated' => 'API key regenerated successfully.',
    ],
    'modals' => [
        'reveal_title'           => 'API key for :name',
        'reveal_description'     => 'Copy and store the secret securely. It is displayed only once.',
        'regenerate_description' => 'Generating a new secret will immediately invalidate the current credentials.',
        'regenerate_warning'     => 'Clients using the previous key will stop working until you update them with the new value.',
    ],
    'rate_limit' => [
        'unlimited' => 'Unlimited',
    ],
    'scopes' => [
        'orders_read' => [
            'label'       => 'Orders (read)',
            'description' => 'Allows retrieving order information.',
        ],
        'orders_write' => [
            'label'       => 'Orders (write)',
            'description' => 'Allows creating or updating order data.',
        ],
        'products_read' => [
            'label'       => 'Products (read)',
            'description' => 'Allows reading product catalog data.',
        ],
        'products_write' => [
            'label'       => 'Products (write)',
            'description' => 'Allows creating or updating product information.',
        ],
        'customers_read' => [
            'label'       => 'Customers (read)',
            'description' => 'Allows accessing customer records.',
        ],
        'customers_write' => [
            'label'       => 'Customers (write)',
            'description' => 'Allows creating or updating customer records.',
        ],
        'analytics_read' => [
            'label'       => 'Analytics (read)',
            'description' => 'Allows retrieving analytics dashboards and metrics.',
        ],
    ],
];
