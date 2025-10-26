<?php

declare(strict_types=1);

return [
    'fields' => [
        'name'           => 'Name',
        'slug'           => 'URL Slug',
        'description'    => 'Description',
        'parent_id'      => 'Parent Category',
        'parent'         => 'Parent Category',
        'sort_order'     => 'Sort Order',
        'color'          => 'Color',
        'icon'           => 'Icon',
        'is_visible'     => 'Visible',
        'news_count'     => 'News Count',
        'children_count' => 'Children Count',
        'created_at'     => 'Created At',
        'updated_at'     => 'Updated At',
    ],
    'filters' => [
        'parent'     => 'Parent Category',
        'is_visible' => 'Visible',
    ],
    'actions' => [
        'create' => 'Create Category',
        'edit'   => 'Edit Category',
        'delete' => 'Delete Category',
        'view'   => 'View Category',
    ],
    'messages' => [
        'created' => 'Category successfully created',
        'updated' => 'Category successfully updated',
        'deleted' => 'Category successfully deleted',
    ],
    'sections' => [
        'category_information'   => 'Category Information',
        'hierarchy_display'      => 'Hierarchy & Display',
        'visibility'             => 'Visibility',
        'statistics'             => 'Statistics',
        'category_details'       => 'Category Details',
        'display_settings'       => 'Display Settings',
        'news_overview'          => 'News Overview',
        'metadata'               => 'Metadata',
        'parent_visibility_hint' => 'Parent Visibility Notice',
    ],
    'helpers' => [
        'name'                     => 'Provide the category name for all languages.',
        'slug'                     => 'Automatically generated from the name. You can adjust it manually if needed.',
        'description'              => 'Brief description that helps visitors understand the category purpose.',
        'parent_id'                => 'Optional parent category. Hidden parents remain selectable for structural purposes.',
        'sort_order'               => 'Lower numbers appear first in listings.',
        'color'                    => 'Select a color to visually distinguish the category in the interface.',
        'icon'                     => 'Choose an icon that represents the category.',
        'is_visible'               => 'Toggle visibility for storefront surfaces. Hidden categories remain available for nesting.',
        'parent_visibility_notice' => 'Hidden parent categories can still be selected for hierarchy management.',
    ],
    'placeholders' => [
        'name'        => 'Enter category name',
        'slug'        => 'enter-url-slug',
        'description' => 'Describe the category purpose',
        'parent_id'   => 'Select parent category',
        'color'       => '#000000',
        'icon'        => 'heroicon-o-tag',
        'sort_order'  => '0',
    ],
];
