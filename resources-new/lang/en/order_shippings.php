<?php

return [
    // Navigation
    'navigation' => [
        'order_shippings' => 'Order Shippings',
    ],

    // Models
    'models' => [
        'order_shipping' => 'Order Shipping',
        'order_shippings' => 'Order Shippings',
    ],

    // Fields
    'fields' => [
        'order' => 'Order',
        'carrier_name' => 'Carrier Name',
        'service' => 'Service',
        'tracking_number' => 'Tracking Number',
        'tracking_url' => 'Tracking URL',
        'shipped_at' => 'Shipped At',
        'estimated_delivery' => 'Estimated Delivery',
        'delivered_at' => 'Delivered At',
        'weight' => 'Weight',
        'cost' => 'Cost',
        'dimensions' => 'Dimensions',
        'metadata' => 'Metadata',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'shipping_information' => 'Shipping Information',
        'tracking_information' => 'Tracking Information',
        'physical_properties' => 'Physical Properties',
    ],

    // Statuses
    'status' => [
        'pending' => 'Pending',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
    ],

    // Actions
    'actions' => [
        'create' => 'Create',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'mark_shipped' => 'Mark as Shipped',
        'mark_delivered' => 'Mark as Delivered',
    ],

    // Bulk actions
    'bulk_mark_shipped' => 'Mark as Shipped',
    'bulk_mark_delivered' => 'Mark as Delivered',

    // Help text
    'carrier_name_help' => 'Carrier name (e.g., DHL, FedEx)',
    'service_help' => 'Shipping service (e.g., Express, Standard)',
    'tracking_number_help' => 'Tracking number',
    'tracking_url_help' => 'Tracking URL',
    'weight_help' => 'Weight in kilograms',
    'cost_help' => 'Shipping cost',
    'dimensions_help' => 'Dimensions (e.g., 30x20x10 cm)',
    'metadata_help' => 'Additional metadata',

    // Filters
    'shipped_from' => 'Shipped From',
    'shipped_until' => 'Shipped Until',
];
