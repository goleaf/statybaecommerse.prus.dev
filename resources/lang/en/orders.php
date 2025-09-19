<?php

declare(strict_types=1);

return [
    // Navigation
    'navigation' => [
        'orders' => 'Orders',
        'order_management' => 'Order Management',
    ],
    
    // Models
    'models' => [
        'order' => 'Order',
        'orders' => 'Orders',
        'order_item' => 'Order Item',
        'order_items' => 'Order Items',
    ],
    
    // Fields
    'fields' => [
        'order_number' => 'Order Number',
        'customer' => 'Customer',
        'customer_name' => 'Customer Name',
        'customer_email' => 'Customer Email',
        'customer_phone' => 'Customer Phone',
        'status' => 'Status',
        'payment_status' => 'Payment Status',
        'shipping_status' => 'Shipping Status',
        'total' => 'Total',
        'subtotal' => 'Subtotal',
        'tax_amount' => 'Tax Amount',
        'shipping_amount' => 'Shipping Amount',
        'discount_amount' => 'Discount Amount',
        'currency' => 'Currency',
        'payment_method' => 'Payment Method',
        'billing_address' => 'Billing Address',
        'shipping_address' => 'Shipping Address',
        'notes' => 'Notes',
        'internal_notes' => 'Internal Notes',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'shipped_at' => 'Shipped At',
        'delivered_at' => 'Delivered At',
        'cancelled_at' => 'Cancelled At',
        'tracking_number' => 'Tracking Number',
        'tracking_url' => 'Tracking URL',
        'carrier' => 'Carrier',
        'service' => 'Service Type',
        'estimated_delivery' => 'Estimated Delivery',
        'weight' => 'Weight',
        'dimensions' => 'Dimensions',
        'items_count' => 'Items Count',
        'total_items' => 'Total Items',
    ],
    
    // Status
    'status' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
        'completed' => 'Completed',
    ],
    
    // Payment Status
    'payment_status' => [
        'pending' => 'Pending Payment',
        'paid' => 'Paid',
        'failed' => 'Payment Failed',
        'refunded' => 'Refunded',
        'partially_refunded' => 'Partially Refunded',
        'cancelled' => 'Cancelled',
    ],
    
    // Payment Methods
    'payment_methods' => [
        'credit_card' => 'Credit Card',
        'paypal' => 'PayPal',
        'bank_transfer' => 'Bank Transfer',
        'cash_on_delivery' => 'Cash on Delivery',
        'stripe' => 'Stripe',
        'mollie' => 'Mollie',
    ],
    
    // Shipping Carriers
    'shipping_carriers' => [
        'dpd' => 'DPD',
        'omniva' => 'Omniva',
        'lp_express' => 'LP Express',
        'ups' => 'UPS',
        'fedex' => 'FedEx',
        'dhl' => 'DHL',
    ],
    
    // Shipping Services
    'shipping_services' => [
        'standard' => 'Standard',
        'express' => 'Express',
        'next_day' => 'Next Day',
        'economy' => 'Economy',
        'premium' => 'Premium',
    ],
    
    // Actions
    'actions' => [
        'create' => 'Create Order',
        'edit' => 'Edit Order',
        'view' => 'View Order',
        'delete' => 'Delete Order',
        'duplicate' => 'Duplicate Order',
        'print_invoice' => 'Print Invoice',
        'print_receipt' => 'Print Receipt',
        'generate_documents' => 'Generate Documents',
        'send_notification' => 'Send Notification',
        'track_shipment' => 'Track Shipment',
        'refund_order' => 'Refund Order',
        'cancel_order' => 'Cancel Order',
        'mark_as_paid' => 'Mark as Paid',
        'mark_as_shipped' => 'Mark as Shipped',
        'mark_as_delivered' => 'Mark as Delivered',
        'export_orders' => 'Export Orders',
    ],
    
    // Filters
    'filters' => [
        'status' => 'Status',
        'payment_status' => 'Payment Status',
        'date_range' => 'Date Range',
        'customer' => 'Customer',
        'payment_method' => 'Payment Method',
        'shipping_carrier' => 'Carrier',
        'total_range' => 'Total Range',
    ],
    
    // Notifications
    'notifications' => [
        'order_created' => 'Order created',
        'order_updated' => 'Order updated',
        'order_cancelled' => 'Order cancelled',
        'order_shipped' => 'Order shipped',
        'order_delivered' => 'Order delivered',
        'payment_received' => 'Payment received',
        'payment_failed' => 'Payment failed',
        'refund_processed' => 'Refund processed',
    ],
    
    // Widgets
    'widgets' => [
        'total_orders' => 'Total Orders',
        'pending_orders' => 'Pending Orders',
        'completed_orders' => 'Completed Orders',
        'cancelled_orders' => 'Cancelled Orders',
        'total_revenue' => 'Total Revenue',
        'average_order_value' => 'Average Order Value',
        'recent_orders' => 'Recent Orders',
        'orders_today' => 'Orders Today',
        'orders_this_month' => 'Orders This Month',
    ],
    
    // Sections
    'sections' => [
        'order_details' => 'Order Details',
        'order_items' => 'Order Items',
        'order_history' => 'Order History',
        'order_timeline' => 'Order Timeline',
        'order_documents' => 'Order Documents',
        'order_shipping' => 'Order Shipping',
        'order_payments' => 'Order Payments',
        'customer_information' => 'Customer Information',
        'billing_information' => 'Billing Information',
        'shipping_information' => 'Shipping Information',
    ],
];

