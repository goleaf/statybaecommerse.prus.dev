<?php

declare(strict_types=1);

return [
    'title'  => 'Document Templates',
    'plural' => 'Document Templates',
    'single' => 'Document Template',

    'basic_information' => 'Basic Information',
    'content'           => 'Content',
    'settings'          => 'Settings',

    'name'        => 'Name',
    'slug'        => 'Slug',
    'description' => 'Description',
    'content'     => 'Content',
    'type'        => 'Type',
    'category'    => 'Category',
    'is_active'   => 'Active',
    'created_at'  => 'Created At',
    'updated_at'  => 'Updated At',

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

    'notifications' => [
        'delete_has_documents' => [
            'title' => 'Cannot delete template',
            'body'  => 'This template is used by existing documents. Please remove the documents first.',
        ],
    ],
];
