<?php

declare(strict_types=1);

return [
    'messages' => [
        'invalid'         => 'This discount code is invalid.',
        'limit_reached'   => 'This discount code has reached its usage limit.',
        'expired'         => 'This discount code has expired.',
        'inactive'        => 'This discount code is currently inactive.',
        'already_used'    => 'You have already used this discount code.',
        'success'         => 'Discount code applied successfully!',
        'minimum_not_met' => 'The minimum order amount for this discount code has not been met.',
        'not_stackable'   => 'This discount code cannot be combined with other discounts.',
        'not_applicable'  => 'This discount code is not applicable to the items in your cart.',
        'removed'         => 'Discount code removed.',
    ],
    'fields' => [
        'code'   => 'Coupon Code',
        'apply'  => 'Apply Coupon',
        'remove' => 'Remove',
    ],
];
