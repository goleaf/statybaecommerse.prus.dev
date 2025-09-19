<?php declare(strict_types=1);

return [
    'title' => 'Enum Management',
    'single' => 'Enum Value',
    'plural' => 'Enum Values',
    // Form sections
    'form' => [
        'sections' => [
            'basic_information' => 'Basic Information',
            'additional_settings' => 'Additional Settings',
            'preview' => 'Preview',
        ],
        'tabs' => [
            'basic_information' => 'Basic Information',
            'additional_settings' => 'Additional Settings',
            'preview' => 'Preview',
        ],
        'fields' => [
            'type' => 'Type',
            'key' => 'Key',
            'value' => 'Value',
            'name' => 'Name',
            'description' => 'Description',
            'sort_order' => 'Sort Order',
            'is_active' => 'Active',
            'is_default' => 'Default',
            'metadata' => 'Metadata',
            'metadata_key' => 'Key',
            'metadata_value' => 'Value',
            'enum_preview' => 'Enum Preview',
            'usage_count' => 'Usage Count',
        ],
    ],
    // Types
    'types' => [
        'navigation_group' => 'Navigation Group',
        'order_status' => 'Order Status',
        'payment_status' => 'Payment Status',
        'shipping_status' => 'Shipping Status',
        'user_role' => 'User Role',
        'product_status' => 'Product Status',
        'campaign_type' => 'Campaign Type',
        'discount_type' => 'Discount Type',
        'notification_type' => 'Notification Type',
        'document_type' => 'Document Type',
        'address_type' => 'Address Type',
        'priority' => 'Priority',
        'status' => 'Status',
    ],
    // Navigation groups
    'navigation_groups' => [
        'products' => 'Products',
        'orders' => 'Orders',
        'customers' => 'Customers',
        'marketing' => 'Marketing',
        'reports' => 'Reports',
        'system' => 'System',
    ],
    // Order statuses
    'order_statuses' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],
    // Payment statuses
    'payment_statuses' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
        'partially_refunded' => 'Partially Refunded',
    ],
    // Shipping statuses
    'shipping_statuses' => [
        'pending' => 'Pending',
        'preparing' => 'Preparing',
        'shipped' => 'Shipped',
        'in_transit' => 'In Transit',
        'delivered' => 'Delivered',
        'returned' => 'Returned',
    ],
    // User roles
    'user_roles' => [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'employee' => 'Employee',
        'customer' => 'Customer',
    ],
    // Product statuses
    'product_statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'draft' => 'Draft',
        'archived' => 'Archived',
    ],
    // Campaign types
    'campaign_types' => [
        'email' => 'Email Campaign',
        'sms' => 'SMS Campaign',
        'social' => 'Social Media',
        'display' => 'Display Ads',
    ],
    // Discount types
    'discount_types' => [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed Amount',
        'free_shipping' => 'Free Shipping',
        'buy_one_get_one' => 'Buy One Get One',
    ],
    // Notification types
    'notification_types' => [
        'order' => 'Order',
        'product' => 'Product',
        'user' => 'User',
        'system' => 'System',
        'payment' => 'Payment',
        'shipping' => 'Shipping',
    ],
    // Document types
    'document_types' => [
        'invoice' => 'Invoice',
        'receipt' => 'Receipt',
        'contract' => 'Contract',
        'report' => 'Report',
    ],
    // Address types
    'address_types' => [
        'billing' => 'Billing Address',
        'shipping' => 'Shipping Address',
        'home' => 'Home Address',
        'work' => 'Work Address',
    ],
    // Priorities
    'priorities' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],
    // Statuses
    'statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending' => 'Pending',
        'completed' => 'Completed',
    ],
    // Filters
    'filters' => [
        'type' => 'Type',
        'is_active' => 'Active',
        'is_default' => 'Default',
        'recent' => 'Recent',
    ],
    // Actions
    'actions' => [
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'set_default' => 'Set as Default',
        'activate_bulk' => 'Activate Bulk',
        'deactivate_bulk' => 'Deactivate Bulk',
    ],
    // Messages
    'activated_successfully' => 'Enum value activated successfully',
    'deactivated_successfully' => 'Enum value deactivated successfully',
    'set_default_successfully' => 'Enum value set as default successfully',
    'bulk_activated_successfully' => 'Enum values activated successfully',
    'bulk_deactivated_successfully' => 'Enum values deactivated successfully',
];
