<?php

declare(strict_types=1);

return [
    'title' => 'Reviews',
    'plural' => 'Reviews',
    'single' => 'Review',
    'fields' => [
        'rating' => 'Rating',
        'title' => 'Title',
        'content' => 'Content',
        'is_approved' => 'Approved',
        'is_featured' => 'Featured',
        'created_at' => 'Created At',
    ],
    'actions' => [
        'approve' => 'Approve',
        'disapprove' => 'Disapprove',
    ],
];
