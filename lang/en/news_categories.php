<?php

return [
    'fields' => [
        'name' => 'Name',
        'slug' => 'URL Slug',
        'description' => 'Description',
        'parent_id' => 'Parent Category',
        'parent' => 'Parent Category',
        'sort_order' => 'Sort Order',
        'color' => 'Color',
        'icon' => 'Icon',
        'is_visible' => 'Visible',
        'news_count' => 'News Count',
        'children_count' => 'Children Count',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'filters' => [
        'parent' => 'Parent Category',
        'is_visible' => 'Visible',
    ],
    'actions' => [
        'create' => 'Create Category',
        'edit' => 'Edit Category',
        'delete' => 'Delete Category',
        'view' => 'View Category',
    ],
    'messages' => [
        'created' => 'Category successfully created',
        'updated' => 'Category successfully updated',
        'deleted' => 'Category successfully deleted',
    ],
    'sections' => [
        'category_information' => 'Category Information',
        'hierarchy_display' => 'Hierarchy & Display',
        'visibility' => 'Visibility',
        'statistics' => 'Statistics',
        'category_details' => 'Category Details',
        'display_settings' => 'Display Settings',
    ],
];
