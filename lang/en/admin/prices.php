<?php

declare(strict_types=1);

return [
    'navigation' => [
        'label' => 'Prices',
    ],

    'model' => [
        'singular' => 'Price',
        'plural'   => 'Prices',
    ],

    'sections' => [
        'basic_information' => 'Basic Information',
        'pricing'           => 'Pricing',
        'validity'          => 'Validity Period',
        'metadata'          => 'Metadata',
    ],

    'fields' => [
        'priceable'      => 'Priceable',
        'priceable_type' => 'Priceable Type',
        'priceable_name' => 'Name',
        'currency'       => 'Currency',
        'type'           => 'Price Type',
        'amount'         => 'Amount',
        'compare_amount' => 'Compare Amount',
        'cost_amount'    => 'Cost Amount',
        'is_enabled'     => 'Enabled',
        'starts_at'      => 'Starts At',
        'ends_at'        => 'Ends At',
        'metadata'       => 'Metadata',
        'created_at'     => 'Created At',
        'updated_at'     => 'Updated At',
    ],

    'filters' => [
        'priceable_type' => 'Priceable Type',
        'currency'       => 'Currency',
        'type'           => 'Price Type',
        'is_enabled'     => 'Enabled Status',
        'active'         => 'Active Prices',
    ],

    'priceable_types' => [
        'product' => 'Product',
        'variant' => 'Variant',
    ],

    'types' => [
        'retail'    => 'Retail',
        'wholesale' => 'Wholesale',
        'special'   => 'Special',
        'sale'      => 'Sale',
    ],
];
