<?php

declare(strict_types=1);

return [
    'states' => [
        'draft'     => 'Draft',
        'review'    => 'In Review',
        'published' => 'Published',
    ],
    'actions' => [
        'submit_for_review' => 'Submit for Review',
        'approve'           => 'Approve & Publish',
        'return_to_draft'   => 'Return to Draft',
        'request_changes'   => 'Request Changes',
    ],
    'messages' => [
        'submitted'         => 'Content submitted for review.',
        'approved'          => 'Content approved and published.',
        'returned'          => 'Content returned to draft.',
        'changes_requested' => 'Changes requested from the author.',
    ],
];
