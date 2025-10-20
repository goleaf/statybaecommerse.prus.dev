<?php

declare(strict_types=1);

return [
    'title'  => 'Product Requests',
    'plural' => 'Product Requests',
    'single' => 'Product Request',
    'fields' => [
        'product'            => 'Product',
        'user'               => 'User',
        'name'               => 'Name',
        'email'              => 'Email',
        'phone'              => 'Phone',
        'message'            => 'Message',
        'requested_quantity' => 'Requested Quantity',
        'status'             => 'Status',
        'admin_notes'        => 'Admin Notes',
        'responded_at'       => 'Responded At',
        'responded_by'       => 'Responded By',
        'created_at'         => 'Created At',
    ],
    'status' => [
        'pending'     => 'Pending',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'cancelled'   => 'Cancelled',
    ],
    'filters' => [
        'status'  => 'Status',
        'product' => 'Product',
        'user'    => 'User',
    ],
];
