<?php

return [
    'version' => 'v1',

    'entities' => [
        'product' => [
            'schema' => 'docs/contracts/entities/v1/product.schema.json',
            'examples' => [
                'minimal' => 'docs/contracts/examples/v1/product-minimal.json',
                'full' => 'docs/contracts/examples/v1/product-full.json',
            ],
        ],
        'category' => [
            'schema' => 'docs/contracts/entities/v1/category.schema.json',
            'examples' => [
                'minimal' => 'docs/contracts/examples/v1/category-minimal.json',
                'full' => 'docs/contracts/examples/v1/category-full.json',
            ],
        ],
        'brand' => [
            'schema' => 'docs/contracts/entities/v1/brand.schema.json',
            'examples' => [
                'minimal' => 'docs/contracts/examples/v1/brand-minimal.json',
                'full' => 'docs/contracts/examples/v1/brand-full.json',
            ],
        ],
        'order' => [
            'schema' => 'docs/contracts/entities/v1/order.schema.json',
            'examples' => [
                'minimal' => 'docs/contracts/examples/v1/order-minimal.json',
                'full' => 'docs/contracts/examples/v1/order-full.json',
            ],
        ],
        'user' => [
            'schema' => 'docs/contracts/entities/v1/user.schema.json',
            'examples' => [
                'minimal' => 'docs/contracts/examples/v1/user-minimal.json',
                'full' => 'docs/contracts/examples/v1/user-full.json',
            ],
        ],
    ],
];
