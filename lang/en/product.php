<?php

declare(strict_types=1);

return [
    'variants' => [
        'fields' => [
            'apply_to_sale_items' => 'Apply to sale items',
            'change_reason'       => 'Change reason',
            'price_type'          => 'Price type',
            'sale_end_date'       => 'Sale end date',
            'sale_start_date'     => 'Sale start date',
            'set_sale_period'     => 'Set sale period',
            'update_type'         => 'Update type',
            'update_value'        => 'Update value',
        ],
        'help' => [
            'update_value' => 'Value used to calculate new prices for selected variants.',
        ],
        'placeholders' => [
            'change_reason' => 'Optional reason for this bulk update...',
        ],
        'price_types' => [
            'regular'     => 'Regular price',
            'wholesale'   => 'Wholesale price',
            'member'      => 'Member price',
            'promotional' => 'Promotional price',
        ],
        'update_types' => [
            'fixed_amount' => 'Add fixed amount',
            'percentage'   => 'Adjust by percentage',
            'multiply_by'  => 'Multiply by value',
            'set_to'       => 'Set exact value',
        ],
        'defaults' => [
            'bulk_price_update_reason' => 'Bulk price update',
        ],
        'notifications' => [
            'bulk_update_success'      => 'Bulk price update completed',
            'bulk_update_success_body' => 'Updated :updated variants. Skipped :skipped variants.',
        ],
        'stats' => [
            'all_variants'          => 'All variants',
            'all_variants_stock'    => 'Stock across all variants',
            'available_stock'       => 'Available stock',
            'average_price'         => 'Average price',
            'between_50_100_euros'  => 'Between €50 and €100',
            'discounted_variants'   => 'Discounted variants',
            'from_sales'            => 'From sales',
            'highest_price'         => 'Highest price',
            'low_stock_alerts'      => 'Low stock alerts',
            'lowest_price'          => 'Lowest price',
            'most_affordable'       => 'Most affordable',
            'most_expensive'        => 'Most expensive',
            'need_restocking'       => 'Need restocking',
            'on_sale'               => 'On sale',
            'out_of_stock'          => 'Out of stock',
            'pending_orders'        => 'Pending orders',
            'price_range_50_100'    => 'Price range €50-€100',
            'price_range_under_50'  => 'Price range under €50',
            'ready_for_sale'        => 'Ready for sale',
            'reserved_stock'        => 'Reserved stock',
            'sold_stock'            => 'Sold stock',
            'stock_value'           => 'Stock value',
            'total_inventory_value' => 'Total inventory value',
            'total_revenue'         => 'Total revenue',
            'total_sold'            => 'Total sold',
            'total_stock'           => 'Total stock',
            'unavailable_variants'  => 'Unavailable variants',
            'under_50_euros'        => 'Under €50',
        ],
        'messages' => [
            'select_variant' => 'Please select a variant',
        ],
    ],
];
