<?php declare(strict_types=1);

return [
    // Navigation
    'title' => 'Order Items',
    'plural' => 'Order Items',
    'single' => 'Order Item',
    // Sections
    'basic_information' => 'Basic Information',
    'pricing' => 'Pricing',
    'additional_information' => 'Additional Information',
    // Fields
    'order' => 'Order',
    'product' => 'Product',
    'product_variant' => 'Product Variant',
    'product_name' => 'Product Name',
    'product_sku' => 'Product SKU',
    'quantity' => 'Quantity',
    'unit_price' => 'Unit Price',
    'discount_amount' => 'Discount Amount',
    'total' => 'Total',
    'notes' => 'Notes',
    // Table columns
    'order_number' => 'Order Number',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'created_from' => 'Created From',
    'created_until' => 'Created Until',
    // Actions
    'create' => 'Create Order Item',
    'edit' => 'Edit Order Item',
    'view' => 'View Order Item',
    'delete' => 'Delete Order Item',
    // Messages
    'created_successfully' => 'Order item created successfully',
    'updated_successfully' => 'Order item updated successfully',
    'deleted_successfully' => 'Order item deleted successfully',
    'bulk_deleted_successfully' => 'Selected order items deleted successfully',
    // Validation
    'validation' => [
        'order_id_required' => 'Order is required',
        'product_id_required' => 'Product is required',
        'quantity_required' => 'Quantity is required',
        'quantity_min' => 'Quantity must be at least 1',
        'unit_price_required' => 'Unit price is required',
        'unit_price_min' => 'Unit price must be at least 0',
    ],
    // Widgets
    'widgets' => [
        'total_items' => 'Total Items',
        'total_value' => 'Total Value',
        'average_item_value' => 'Average Item Value',
        'top_products' => 'Top Products',
        'recent_items' => 'Recent Items',
    ],
];

