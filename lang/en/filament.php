<?php

return [
    'navigation' => [
        'content_management' => 'Content Management',
        'news' => 'News',
    ],

    'news' => [
        'model_label' => 'News Article',
        'plural_model_label' => 'News Articles',

        'sections' => [
            'basic_information' => 'Basic Information',
            'translations' => 'Translations',
            'categories_and_tags' => 'Categories and Tags',
            'images' => 'Images',
            'meta_data' => 'Meta Data',
        ],

        'fields' => [
            'author_name' => 'Author Name',
            'author_email' => 'Author Email',
            'is_visible' => 'Visible',
            'is_featured' => 'Featured',
            'published_at' => 'Published At',
            'translations' => 'Translations',
            'locale' => 'Locale',
            'title' => 'Title',
            'slug' => 'URL Slug',
            'summary' => 'Summary',
            'content' => 'Content',
            'seo_title' => 'SEO Title',
            'seo_description' => 'SEO Description',
            'categories' => 'Categories',
            'tags' => 'Tags',
            'images' => 'Images',
            'image_file' => 'Image File',
            'alt_text' => 'Alt Text',
            'caption' => 'Caption',
            'is_featured_image' => 'Featured Image',
            'sort_order' => 'Sort Order',
            'meta_data' => 'Meta Data',
            'meta_key' => 'Key',
            'meta_value' => 'Value',
            'featured_image' => 'Featured Image',
            'view_count' => 'View Count',
            'created_at' => 'Created At',
        ],

        'filters' => [
            'published_at' => 'Published Date',
            'published_from' => 'Published From',
            'published_until' => 'Published Until',
            'view_count' => 'View Count',
            'view_count_from' => 'View Count From',
            'view_count_to' => 'View Count To',
        ],

        'actions' => [
            'duplicate' => 'Duplicate',
            'mark_visible' => 'Mark as Visible',
            'mark_hidden' => 'Mark as Hidden',
            'mark_featured' => 'Mark as Featured',
            'unmark_featured' => 'Unmark as Featured',
            'delete' => 'Delete',
        ],
    ],
];

