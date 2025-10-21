<?php

declare(strict_types=1);

return array_replace_recursive(
    require __DIR__ . '/discount_codes.php',
    [
        'title'  => 'Coupons',
        'plural' => 'Coupons',
        'single' => 'Coupon',
        'badges' => [
            'type'           => 'Type: :type',
            'customer_group' => 'Group: :group',
            'public_scope'   => 'All customers',
            'active'         => 'Active',
            'inactive'       => 'Inactive',
            'used_of_limit'  => 'Used: :count / :limit',
            'used'           => 'Used: :count',
            'remaining'      => 'Remaining: :count',
            'public'         => 'Public',
            'private'        => 'Private',
            'auto_apply'     => 'Auto apply',
            'manual_apply'   => 'Manual apply',
            'stackable'      => 'Stackable',
            'single_use'     => 'Single use',
        ],
    ]
);
