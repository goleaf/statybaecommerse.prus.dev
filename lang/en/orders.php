<?php

declare(strict_types=1);

return [
    // Navigation
    'navigation_label'   => 'Orders',
    'navigation_group'   => 'E-commerce',
    'model_label'        => 'Order',
    'plural_model_label' => 'Orders',

    // Form sections
    'basic_information'      => 'Basic Information',
    'financial_information'  => 'Financial Information',
    'addresses'              => 'Addresses',
    'additional_information' => 'Additional Information',
    'item_information'       => 'Item Information',
    'shipping_details'       => 'Shipping Details',
    'document_information'   => 'Document Information',

    // Fields
    'number'            => 'Number',
    'customer'          => 'Customer',
    'status'            => 'Status',
    'payment_status'    => 'Payment Status',
    'payment_method'    => 'Payment Method',
    'payment_reference' => 'Payment Reference',
    'subtotal'          => 'Subtotal',
    'tax_amount'        => 'Tax Amount',
    'shipping_amount'   => 'Shipping Amount',
    'discount_amount'   => 'Discount Amount',
    'total'             => 'Total',
    'currency'          => 'Currency',
    'billing_address'   => 'Billing Address',
    'shipping_address'  => 'Shipping Address',
    'notes'             => 'Notes',
    'channel'           => 'Channel',
    'zone'              => 'Zone',
    'partner'           => 'Partner',
    'shipped_at'        => 'Shipped At',
    'delivered_at'      => 'Delivered At',
    'created_at'        => 'Created At',
    'created_from'      => 'Created From',
    'created_until'     => 'Created Until',

    // Statuses
    'statuses' => [
        'pending'    => 'Pending',
        'processing' => 'Processing',
        'confirmed'  => 'Confirmed',
        'shipped'    => 'Shipped',
        'delivered'  => 'Delivered',
        'completed'  => 'Completed',
        'cancelled'  => 'Cancelled',
        'returned'   => 'Returned',
    ],

    // Payment statuses
    'payment_statuses' => [
        'pending'            => 'Pending',
        'paid'               => 'Paid',
        'failed'             => 'Failed',
        'refunded'           => 'Refunded',
        'partially_refunded' => 'Partially Refunded',
    ],
    'badges' => [
        'payment'        => 'Payment: :status',
        'payment_method' => 'Method: :method',
        'channel'        => 'Channel: :channel',
        'shipping'       => [
            'pending'   => 'Shipment pending',
            'shipped'   => 'Shipped on :date',
            'delivered' => 'Delivered on :date',
        ],
        'total'          => 'Total: :total',
        'items'          => '{0} No items|{1} 1 item|[2,*] :count items',
        'status_tooltip' => 'Order :number status summary',
    ],

    // Order items
    'order_items'     => 'Order Items',
    'order_item'      => 'Order Item',
    'product'         => 'Product',
    'product_name'    => 'Product Name',
    'product_variant' => 'Product Variant',
    'sku'             => 'SKU',
    'quantity'        => 'Quantity',
    'unit_price'      => 'Unit Price',
    'price'           => 'Price',
    'items_count'     => 'Items Count',

    // Shipping
    'shipping_information' => 'Shipping Information',
    'shipping'             => 'Shipping',
    'carrier_name'         => 'Carrier Name',
    'service'              => 'Service',
    'tracking_number'      => 'Tracking Number',
    'tracking_url'         => 'Tracking URL',
    'estimated_delivery'   => 'Estimated Delivery',
    'shipping_cost'        => 'Shipping Cost',
    'weight'               => 'Weight',
    'dimensions'           => 'Dimensions',
    'dimension_type'       => 'Dimension Type',
    'dimension_value'      => 'Dimension Value',
    'add_dimension'        => 'Add Dimension',
    'metadata'             => 'Metadata',
    'metadata_key'         => 'Metadata Key',
    'metadata_value'       => 'Metadata Value',
    'add_metadata'         => 'Add Metadata',
    'shipping_status'      => 'Shipping Status',

    // Documents
    'documents'                => 'Documents',
    'document'                 => 'Document',
    'document_name'            => 'Document Name',
    'document_type'            => 'Document Type',
    'document_file'            => 'Document File',
    'file_size'                => 'File Size',
    'mime_type'                => 'MIME Type',
    'download'                 => 'Download',
    'description'              => 'Description',
    'document_version_tooltip' => 'Open generated document version :version',

    // Document types
    'document_types' => [
        'invoice'        => 'Invoice',
        'receipt'        => 'Receipt',
        'shipping_label' => 'Shipping Label',
        'return_label'   => 'Return Label',
        'warranty'       => 'Warranty',
        'manual'         => 'Manual',
        'other'          => 'Other',
    ],

    // Address fields
    'address_field'     => 'Field Name',
    'address_value'     => 'Field Value',
    'add_address_field' => 'Add Address Field',

    'lookups' => [
        'billing_address'     => 'Search billing address',
        'shipping_address'    => 'Search shipping address',
        'address_placeholder' => 'Start typing an address, city, or postal code…',
        'address_field'       => 'Field',
        'address_value'       => 'Value',
        'channel_placeholder' => 'Search channels by name or code…',
        'channel_unknown'     => 'Unnamed channel',
        'partner_placeholder' => 'Search partners by name, code, or email…',
        'partner_unknown'     => 'Unnamed partner',
        'status_placeholder'  => 'Search order statuses…',
        'variant_placeholder' => 'Search variants by SKU or name…',
        'variant_product'     => 'Product: :product',
        'variant_unknown'     => 'Variant',
    ],

    // Actions
    'actions' => [
        'view'              => 'View',
        'edit'              => 'Edit',
        'delete'            => 'Delete',
        'mark_shipped'      => 'Mark as Shipped',
        'mark_delivered'    => 'Mark as Delivered',
        'cancel'            => 'Cancel',
        'bulk_mark_shipped' => 'Bulk Mark as Shipped',
    ],

    // Tabs
    'tabs' => [
        'all'        => 'All',
        'pending'    => 'Pending',
        'processing' => 'Processing',
        'shipped'    => 'Shipped',
        'delivered'  => 'Delivered',
        'completed'  => 'Completed',
        'cancelled'  => 'Cancelled',
    ],

    // Stats
    'stats' => [
        'total_orders'         => 'Total Orders',
        'pending_orders'       => 'Pending Orders',
        'processing_orders'    => 'Processing Orders',
        'shipped_orders'       => 'Shipped Orders',
        'delivered_orders'     => 'Delivered Orders',
        'completed_orders'     => 'Delivered Orders',
        'cancelled_orders'     => 'Cancelled Orders',
        'total_revenue'        => 'Total Revenue',
        'average_order_value'  => 'Average Order Value',
        'today_orders'         => 'Today\'s Orders',
        'this_week_orders'     => 'This Week\'s Orders',
        'this_month_orders'    => 'This Month\'s Orders',
        'all_time'             => 'All Time',
        'need_attention'       => 'Need Attention',
        'in_progress'          => 'In Progress',
        'in_transit'           => 'In Transit',
        'completed_deliveries' => 'Completed Deliveries',
        'fully_completed'      => 'Fully Completed',
        'cancelled'            => 'Cancelled',
        'lifetime_revenue'     => 'Lifetime Revenue',
        'per_order'            => 'Per Order',
        'today'                => 'Today',
        'this_week'            => 'This Week',
        'this_month'           => 'This Month',
    ],

    // Charts
    'charts' => [
        'orders_over_time' => 'Orders Over Time',
        'orders_count'     => 'Orders Count',
        'revenue'          => 'Revenue',
    ],

    // Widgets
    'widgets' => [
        'recent_orders' => 'Recent Orders',
    ],

    // Filters
    'is_paid' => 'Is Paid',

    // Frontend specific
    'my_orders'             => 'My Orders',
    'manage_your_orders'    => 'Manage your orders',
    'search'                => 'Search',
    'search_placeholder'    => 'Search by number or notes...',
    'all_statuses'          => 'All Statuses',
    'filter'                => 'Filter',
    'order'                 => 'Order',
    'placed_on'             => 'Placed on',
    'items'                 => 'Items',
    'and_more_items'        => 'and :count more items',
    'view_details'          => 'View Details',
    'confirm_cancel'        => 'Are you sure you want to cancel this order?',
    'cancel_order'          => 'Cancel Order',
    'no_orders'             => 'No Orders',
    'no_orders_description' => 'You don\'t have any orders yet. Start shopping!',
    'start_shopping'        => 'Start Shopping',
    'order_details'         => 'Order Details #:number',
    'order_summary'         => 'Order Summary',
    'track_package'         => 'Track Package',
    'back_to_orders'        => 'Back to Orders',
    'actions'               => 'Actions',

    // Messages
    'messages' => [
        'created_successfully'          => 'Order created successfully',
        'creation_failed'               => 'Failed to create order',
        'updated_successfully'          => 'Order updated successfully',
        'update_failed'                 => 'Failed to update order',
        'deleted_successfully'          => 'Order deleted successfully',
        'cannot_edit'                   => 'You cannot edit this order',
        'cannot_delete'                 => 'You cannot delete this order',
        'cannot_cancel'                 => 'You cannot cancel this order',
        'cancelled_successfully'        => 'Order cancelled successfully',
        'cannot_request_return'         => 'You cannot request a return for this order',
        'return_requested_successfully' => 'Return request submitted successfully',
    ],
];
