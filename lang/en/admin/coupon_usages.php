<?php

return [
    'title' => 'Coupon Usage',
    'plural' => 'Coupon Usages',
    'single' => 'Coupon Usage',
    'form' => [
        'tabs' => [
            'basic_information' => 'Basic Information',
            'usage_details' => 'Usage Details',
            'metadata' => 'Metadata',
        ],
        'sections' => [
            'basic_information' => 'Basic Information',
            'usage_details' => 'Usage Details',
            'metadata' => 'Metadata',
        ],
        'fields' => [
            'coupon' => 'Coupon',
            'user' => 'User',
            'order' => 'Order',
            'discount_amount' => 'Discount Amount',
            'used_at' => 'Used At',
            'coupon_name' => 'Coupon Name',
            'coupon_discount_type' => 'Discount Type',
            'user_email' => 'User Email',
            'order_total' => 'Order Total',
            'metadata' => 'Metadata',
            'key' => 'Key',
            'value' => 'Value',
        ],
    ],
    'periods' => [
        'today' => 'Today',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'older' => 'Older',
    ],
    'filters' => [
        'coupon' => 'Coupon',
        'user' => 'User',
        'order' => 'Order',
        'used_at' => 'Used At',
        'used_today' => 'Used Today',
        'used_this_week' => 'Used This Week',
        'used_this_month' => 'Used This Month',
    ],
    'actions' => [
        'export_usage_report' => 'Export Usage Report',
        'export_bulk_report' => 'Export Bulk Report',
    ],
    'notifications' => [
        'usage_report_exported_successfully' => 'Usage report exported successfully',
        'bulk_report_exported_successfully' => 'Bulk report exported successfully',
    ],
];
