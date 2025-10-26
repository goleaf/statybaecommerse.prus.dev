<?php

declare(strict_types=1);

return [
    'notifications' => [
        'completed' => [
            'subject' => 'Your export ":name" is ready',
            'intro'   => 'The export you requested has finished processing.',
            'format'  => 'Format: :format',
            'action'  => 'Download export',
            'expires' => 'The link will expire in :minutes minutes.',
        ],
        'failed' => [
            'subject' => 'Export ":name" failed',
            'intro'   => 'We were unable to generate the export you requested.',
            'reason'  => 'Reason: :reason',
            'support' => 'Please try again or contact support if the problem persists.',
        ],
    ],
    'filament' => [
        'bulk_action' => [
            'label'             => 'Export selected',
            'modal_heading'     => 'Export selected :label',
            'modal_description' => 'Choose the format and columns you want to export.',
            'success'           => 'Export queued successfully. You will be notified once it is ready.',
            'success_body'      => 'We will email you a download link as soon as the export finishes processing.',
            'format_label'      => 'Format',
            'columns_label'     => 'Columns',
            'columns_help'      => 'Select the columns to include in the export file.',
        ],
    ],
];
