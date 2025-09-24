<?php

return [
    'title' => 'Reviews',
    'plural' => 'Reviews',
    'single' => 'Review',

    'sections' => [
        'basic_info' => 'Basic Information',
        'basic_info_description' => 'Essential details about the review',
        'content' => 'Content',
        'content_description' => 'Review title and content',
        'status' => 'Status',
        'status_description' => 'Approval and feature status',
        'advanced' => 'Advanced Settings',
        'advanced_description' => 'Metadata and additional information',
        'timestamps' => 'Timestamps',
    ],

    'fields' => [
        'product_id' => 'Product',
        'product_name' => 'Product Name',
        'user_id' => 'User',
        'user_name' => 'User Name',
        'reviewer_name' => 'Reviewer Name',
        'reviewer_email' => 'Reviewer Email',
        'rating' => 'Rating',
        'title' => 'Title',
        'content' => 'Content',
        'is_approved' => 'Approved',
        'is_featured' => 'Featured',
        'locale' => 'Locale',
        'metadata' => 'Metadata',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'status' => [
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'pending' => 'Pending',
    ],

    'filters' => [
        'is_approved' => 'Approved',
        'is_featured' => 'Featured',
        'rating' => 'Rating',
        'product' => 'Product',
        'user' => 'User',
        'locale' => 'Locale',
        'high_rated' => 'High Rated (4+ stars)',
        'low_rated' => 'Low Rated (2- stars)',
        'recent' => 'Recent (last 30 days)',
    ],

    'actions' => [
        'approve' => 'Approve',
        'reject' => 'Reject',
        'feature' => 'Feature',
        'unfeature' => 'Unfeature',
        'approve_selected' => 'Approve Selected',
        'reject_selected' => 'Reject Selected',
        'feature_selected' => 'Feature Selected',
        'unfeature_selected' => 'Unfeature Selected',
    ],

    'notifications' => [
        'approved_successfully' => 'Review approved successfully',
        'rejected_successfully' => 'Review rejected successfully',
        'featured_successfully' => 'Review featured successfully',
        'unfeatured_successfully' => 'Review unfeatured successfully',
        'bulk_approved_successfully' => 'Selected reviews approved successfully',
        'bulk_rejected_successfully' => 'Selected reviews rejected successfully',
        'bulk_featured_successfully' => 'Selected reviews featured successfully',
        'bulk_unfeatured_successfully' => 'Selected reviews unfeatured successfully',
    ],

    'placeholders' => [
        'guest_user' => 'Guest User',
        'no_content' => 'No content',
        'no_metadata' => 'No metadata',
        'metadata_json' => 'Enter JSON metadata',
    ],
];
