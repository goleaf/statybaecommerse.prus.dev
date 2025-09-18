<?php

declare(strict_types=1);

return [
    'navigation' => [
        'campaigns' => 'Campaigns',
    ],

    'models' => [
        'campaign' => 'Campaign',
        'campaigns' => 'Campaigns',
    ],

    'sections' => [
        'basic_information' => 'Basic information',
        'campaign_settings' => 'Campaign settings',
        'targeting' => 'Targeting',
        'content' => 'Content',
        'seo' => 'SEO',
    ],

    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'status' => 'Status',
        'is_active' => 'Active',
        'is_featured' => 'Featured',
        'social_media_ready' => 'Ready for social media',
        'start_date' => 'Start date',
        'end_date' => 'End date',
        'max_uses' => 'Maximum uses',
        'budget_limit' => 'Budget limit',
        'send_notifications' => 'Send notifications',
        'track_conversions' => 'Track conversions',
        'auto_pause_on_budget' => 'Pause when budget is reached',
        'target_categories' => 'Target categories',
        'target_products' => 'Target products',
        'target_customer_groups' => 'Target customer groups',
        'channel' => 'Channel',
        'zone' => 'Zone',
        'discounts' => 'Discounts',
        'description' => 'Description',
        'cta_text' => 'Call-to-action text',
        'cta_url' => 'Call-to-action URL',
        'banner' => 'Banner image',
        'banner_alt_text' => 'Banner alternative text',
        'display_priority' => 'Display priority',
        'auto_start' => 'Auto start',
        'auto_end' => 'Auto end',
        'meta_title' => 'Meta title',
        'meta_description' => 'Meta description',
        'total_views' => 'Total views',
        'conversion_rate' => 'Conversion rate',
        'updated_at' => 'Updated',
    ],

    'status' => [
        'draft' => 'Draft',
        'active' => 'Active',
        'scheduled' => 'Scheduled',
        'paused' => 'Paused',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'filters' => [
        'active' => 'Active only',
        'inactive' => 'Inactive only',
    ],

    'tabs' => [
        'all' => 'All campaigns',
        'active' => 'Active',
        'scheduled' => 'Scheduled',
        'draft' => 'Draft',
        'paused' => 'Paused',
        'inactive' => 'Inactive',
        'featured' => 'Featured',
    ],
];
