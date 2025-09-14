<?php

return [
    // Basic fields
    'title' => 'Reviews',
    'review' => 'Review',
    'product' => 'Product',
    'reviewer' => 'Reviewer',
    'reviewer_name' => 'Reviewer Name',
    'reviewer_email' => 'Reviewer Email',
    'rating' => 'Rating',
    'review_title' => 'Review Title',
    'review_comment' => 'Review Comment',
    'approved' => 'Approved',
    'featured' => 'Featured',
    'locale' => 'Locale',
    'approved_at' => 'Approved At',
    'rejected_at' => 'Rejected At',

    // Status
    'status' => [
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'pending' => 'Pending',
        'unknown' => 'Unknown',
    ],

    // Ratings
    'ratings' => [
        'poor' => 'Poor',
        'fair' => 'Fair',
        'good' => 'Good',
        'very_good' => 'Very Good',
        'excellent' => 'Excellent',
        'unknown' => 'Unknown',
    ],

    // Actions
    'actions' => [
        'create_review' => 'Create Review',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'feature' => 'Feature',
        'unfeature' => 'Unfeature',
        'approve_selected' => 'Approve Selected',
        'reject_selected' => 'Reject Selected',
        'feature_selected' => 'Feature Selected',
        'unfeature_selected' => 'Unfeature Selected',
    ],

    // Filters
    'filters' => [
        'approved_only' => 'Approved Only',
        'pending_only' => 'Pending Only',
        'featured_only' => 'Featured Only',
        'high_rated_only' => 'High Rated Only',
        'low_rated_only' => 'Low Rated Only',
        'recent_only' => 'Recent Only',
    ],

    // Settings
    'review_settings' => 'Review Settings',
    'review_settings_description' => 'Configure the basic settings for this review',
    'rating_help' => 'Rating must be between 1 and 5 stars',
    'approved_help' => 'Whether this review is approved for display',
    'featured_help' => 'Whether this review should be featured',

    // Statistics
    'stats' => [
        'total_reviews' => 'Total Reviews',
        'total_reviews_description' => 'All reviews in the system',
        'approved_reviews' => 'Approved Reviews',
        'approved_reviews_description' => 'Reviews approved for display',
        'pending_reviews' => 'Pending Reviews',
        'pending_reviews_description' => 'Reviews awaiting approval',
        'featured_reviews' => 'Featured Reviews',
        'featured_reviews_description' => 'Reviews marked as featured',
        'average_rating' => 'Average Rating',
        'average_rating_description' => 'Average rating across all approved reviews',
    ],

    // Widgets
    'widgets' => [
        'rating_distribution' => 'Rating Distribution',
        'review_count' => 'Review Count',
    ],

    // Empty states
    'empty_states' => [
        'no_reviews' => 'No reviews found',
        'no_pending_reviews' => 'No pending reviews',
        'no_featured_reviews' => 'No featured reviews',
    ],

    // Messages
    'messages' => [
        'created' => 'Review created successfully',
        'updated' => 'Review updated successfully',
        'deleted' => 'Review deleted successfully',
        'approved' => 'Review approved successfully',
        'rejected' => 'Review rejected successfully',
        'featured' => 'Review featured successfully',
        'unfeatured' => 'Review unfeatured successfully',
    ],

    // Validation
    'validation' => [
        'rating_required' => 'Rating is required',
        'rating_min' => 'Rating must be at least 1',
        'rating_max' => 'Rating must be at most 5',
        'title_required' => 'Review title is required',
        'comment_required' => 'Review comment is required',
        'product_required' => 'Product is required',
        'reviewer_name_required' => 'Reviewer name is required',
        'reviewer_email_required' => 'Reviewer email is required',
        'reviewer_email_email' => 'Reviewer email must be a valid email address',
    ],
];
