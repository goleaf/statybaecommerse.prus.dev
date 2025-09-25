<?php declare(strict_types=1);

return [
    // Navigation
    'navigation' => [
        'orders' => 'Orders',
    ],
    // Models
    'models' => [
        'order' => 'Order',
        'orders' => 'Orders',
    ],
    // Fields
    'fields' => [
        'order_number' => 'Order Number',
        'customer' => 'Customer',
        'customer_name' => 'Customer Name',
        'status' => 'Status',
        'payment_status' => 'Payment Status',
        'payment_method' => 'Payment Method',
        'payment_reference' => 'Payment Reference',
        'subtotal' => 'Subtotal',
        'tax_amount' => 'Tax Amount',
        'shipping_amount' => 'Shipping Amount',
        'discount_amount' => 'Discount Amount',
        'total' => 'Total',
        'items_count' => 'Items Count',
        'billing_address' => 'Billing Address',
        'shipping_address' => 'Shipping Address',
        'tracking_number' => 'Tracking Number',
        'shipped_at' => 'Shipped At',
        'delivered_at' => 'Delivered At',
        'notes' => 'Notes',
        'internal_notes' => 'Internal Notes',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],
    // Sections
    'sections' => [
        'order_details' => 'Order Details',
        'customer_information' => 'Customer Information',
        'billing_information' => 'Billing Information',
        'shipping_information' => 'Shipping Information',
        'order_shipping' => 'Order Shipping',
    ],
    // Statuses
    'status' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],
    'statuses' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],
    // Payment statuses
    'payment_status' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ],
    'payment_statuses' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ],
    // Payment methods
    'payment_methods' => [
        'credit_card' => 'Credit Card',
        'bank_transfer' => 'Bank Transfer',
        'cash_on_delivery' => 'Cash on Delivery',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe',
        'apple_pay' => 'Apple Pay',
        'google_pay' => 'Google Pay',
    ],
    // Actions
    'actions' => [
        'create' => 'Create',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
    ],
    'mark_processing' => 'Mark as Processing',
    'mark_shipped' => 'Mark as Shipped',
    'mark_delivered' => 'Mark as Delivered',
    'cancel_order' => 'Cancel Order',
    'refund_order' => 'Refund Order',
    // Bulk actions
    'bulk_mark_processing' => 'Mark as Processing',
    'bulk_mark_shipped' => 'Mark as Shipped',
    'bulk_mark_delivered' => 'Mark as Delivered',
    'bulk_cancel' => 'Cancel Orders',
    'export' => 'Export',
    // Notifications
    'processing_success' => 'Order successfully marked as processing',
    'shipped_successfully' => 'Order successfully marked as shipped',
    'delivered_successfully' => 'Order successfully marked as delivered',
    'cancelled_successfully' => 'Order successfully cancelled',
    'refunded_successfully' => 'Order successfully refunded',
    'bulk_processing_success' => 'Orders successfully marked as processing',
    'bulk_shipped_success' => 'Orders successfully marked as shipped',
    'bulk_delivered_success' => 'Orders successfully marked as delivered',
    'bulk_cancelled_success' => 'Orders successfully cancelled',
    'export_success' => 'Orders successfully exported',
    // Filters
    'is_paid' => 'Paid',
    'total_from' => 'Total From',
    'total_until' => 'Total Until',
    // Help text
    'number_help' => 'Unique order number',
    'guest_customer' => 'Guest customer',
];
