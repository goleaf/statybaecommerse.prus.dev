<?php

return [
    'actions' => [
        'export_orders' => 'Export Orders',
        'export_products' => 'Export Products',
        'export_users' => 'Export Users',
    ],
    'form' => [
        'format' => 'File format',
        'columns' => 'Columns',
    ],
    'notifications' => [
        'queued' => 'Export queued',
        'queued_body' => 'You will receive a notification when the file is ready for download.',
        'subject' => 'Your export ":name" is ready',
        'ready' => 'The export ":name" has finished processing.',
        'download_action' => 'Download export',
        'expiration' => 'This link expires on :date.',
    ],
    'boolean' => [
        'yes' => 'Yes',
        'no' => 'No',
    ],
];
