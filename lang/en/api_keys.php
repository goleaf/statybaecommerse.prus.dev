<?php

return [
    'navigation' => 'API Keys',
    'plural' => 'API Keys',
    'single' => 'API Key',
    'fields' => [
        'name' => 'Name',
        'rate_limit' => 'Rate limit',
        'scopes' => 'Scopes',
        'is_active' => 'Active',
        'last_used_at' => 'Last used',
        'masked_key' => 'Masked key',
        'key' => 'API key',
        'secret' => 'API secret',
    ],
    'helpers' => [
        'rate_limit' => 'Leave empty to allow unlimited requests. Set an integer to throttle requests per minute.',
        'scopes' => 'Choose the capabilities that this key should be allowed to access.',
    ],
    'sections' => [
        'details' => 'Key details',
        'credentials' => 'Credentials & security',
    ],
    'messages' => [
        'no_key' => 'The raw key will be shown once after saving.',
        'unlimited' => 'Unlimited',
        'requests_per_minute' => ':value req/min',
        'copied' => 'Copied!',
        'secret_warning' => 'Store this secret securely. It will not be shown again.',
        'generate_after_save' => 'Save the key to generate credentials. You can reveal them once after creation.',
        'key_modal_hint' => 'Reveal or regenerate the credentials securely. Copy them immediately – they will only be displayed while this dialog is open.',
    ],
    'actions' => [
        'reveal_key' => 'Reveal key',
        'regenerate_key' => 'Regenerate key',
        'copy' => 'Copy',
        'close' => 'Close',
        'reveal_secret' => 'Reveal secret',
        'hide_secret' => 'Hide secret',
        'reactivate' => 'Reactivate',
        'revoke' => 'Revoke',
    ],
    'modals' => [
        'reveal_key' => [
            'heading' => 'API credentials',
        ],
    ],
    'notifications' => [
        'regenerated' => [
            'title' => 'API key regenerated',
            'body' => 'New key: :key',
        ],
    ],
    'scopes' => [
        'read_products' => 'Read products',
        'write_products' => 'Manage products',
        'read_orders' => 'Read orders',
        'manage_orders' => 'Manage orders',
        'manage_customers' => 'Manage customers',
        'access_analytics' => 'Access analytics',
    ],
];
