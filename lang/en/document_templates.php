<?php

declare(strict_types=1);

return [
    'title'  => 'Document Templates',
    'plural' => 'Document Templates',
    'single' => 'Document Template',

    'basic_information' => 'Basic Information',
    'content'           => 'Content',
    'settings'          => 'Settings',

    'name'            => 'Name',
    'slug'            => 'Slug',
    'description'     => 'Description',
    'content'         => 'Content',
    'type'            => 'Type',
    'category'        => 'Category',
    'is_active'       => 'Active',
    'documents_count' => 'Documents',
    'created_at'      => 'Created At',
    'updated_at'      => 'Updated At',

    'types' => [
        'invoice'  => 'Invoice',
        'receipt'  => 'Receipt',
        'quote'    => 'Quote',
        'contract' => 'Contract',
        'report'   => 'Report',
    ],

    'categories' => [
        'financial'   => 'Financial',
        'legal'       => 'Legal',
        'marketing'   => 'Marketing',
        'operational' => 'Operational',
    ],

    'actions' => [
        'preview'    => 'Preview',
        'duplicate'  => 'Duplicate',
        'activate'   => 'Activate',
        'deactivate' => 'Deactivate',
        'export'     => 'Export',
    ],

    'notifications' => [
        'previewed'   => 'Template preview opened successfully.',
        'duplicated'  => 'Template duplicated successfully.',
        'activated'   => 'Selected templates activated successfully.',
        'deactivated' => 'Selected templates deactivated successfully.',
    ],

    'copy_suffix' => '(Copy)',
];
