<?php

declare(strict_types=1);

return [
    'navigation_label'   => 'Product History',
    'plural_model_label' => 'Product Histories',
    'model_label'        => 'Product History',
    'actions'            => [
        'created'          => 'Created',
        'updated'          => 'Updated',
        'deleted'          => 'Deleted',
        'restored'         => 'Restored',
        'price_changed'    => 'Price Changed',
        'stock_updated'    => 'Stock Updated',
        'stock_changed'    => 'Stock Updated',
        'status_changed'   => 'Status Changed',
        'category_changed' => 'Category Changed',
        'image_changed'    => 'Image Changed',
        'custom'           => 'Custom Action',
    ],
    'fields' => [
        'action'         => 'Action',
        'field_name'     => 'Field Name',
        'old_value'      => 'Old Value',
        'new_value'      => 'New Value',
        'price'          => 'Price',
        'sale_price'     => 'Sale Price',
        'stock_quantity' => 'Stock Quantity',
        'status'         => 'Status',
        'is_visible'     => 'Visibility',
        'description'    => 'Description',
        'name'           => 'Name',
        'category'       => 'Category',
        'image'          => 'Image',
        'metadata'       => 'Metadata',
    ],
    'summaries' => [
        'created' => 'Created :field',
        'deleted' => 'Deleted :field',
        'updated' => 'Updated :field from :from to :to',
    ],
    'impact' => [
        'high'   => 'High impact change',
        'medium' => 'Medium impact change',
        'low'    => 'Low impact change',
    ],
];
