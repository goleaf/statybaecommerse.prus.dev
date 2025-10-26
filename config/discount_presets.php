<?php

declare(strict_types=1);

return [
    // Default presets provide sensible starting points and document structure.
    'defaults' => [
        [
            'name'        => 'Seasonal Sale 10%',
            'description' => 'Apply a 10% discount to all seasonal campaign items.',
            'type'        => 'percentage',
            'value'       => 10,
            'conditions'  => ['applies_to:seasonal'],
        ],
        [
            'name'        => 'Clearance €5',
            'description' => 'Fixed 5 euro reduction for clearance products.',
            'type'        => 'fixed',
            'value'       => 5,
            'conditions'  => ['applies_to:clearance'],
        ],
        [
            'name'        => 'VIP Customer 15%',
            'description' => 'Preferred customers receive a 15% loyalty discount.',
            'type'        => 'percentage',
            'value'       => 15,
            'conditions'  => ['customer_group:vip'],
        ],
    ],
];
