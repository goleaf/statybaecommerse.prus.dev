<?php

return [
    // Basic fields
    'title' => 'Posts',
    'post' => 'Post',
    'fields' => [
        'title' => 'Title',
        'slug' => 'Slug',
        'content' => 'Content',
        'excerpt' => 'Excerpt',
        'status' => 'Status',
        'published_at' => 'Published At',
        'featured' => 'Featured',
        'is_pinned' => 'Pinned',
        'user_id' => 'Author',
        'meta_title' => 'Meta Title',
        'meta_description' => 'Meta Description',
        'tags' => 'Tags',
        'images' => 'Featured Image',
        'gallery' => 'Gallery',
        'views_count' => 'Views',
        'likes_count' => 'Likes',
        'comments_count' => 'Comments',
        'allow_comments' => 'Allow Comments',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    // Status
    'status' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],

    // Actions
    'actions' => [
        'publish' => 'Publish',
        'unpublish' => 'Unpublish',
        'archive' => 'Archive',
        'feature' => 'Feature',
        'unfeature' => 'Unfeature',
        'pin' => 'Pin',
        'unpin' => 'Unpin',
    ],

    // Filters
    'filters' => [
        'status' => 'Status',
        'featured' => 'Featured',
        'all_posts' => 'All Posts',
        'featured_only' => 'Featured Only',
        'not_featured' => 'Not Featured',
        'author' => 'Author',
        'published_from' => 'Published From',
        'published_until' => 'Published Until',
    ],

    // SEO
    'seo' => [
        'meta_title_help' => 'Recommended length: 50-60 characters',
        'meta_description_help' => 'Recommended length: 150-160 characters',
    ],

    // Widgets
    'widgets' => [
        'total_posts' => 'Total Posts',
        'published_posts' => 'Published Posts',
        'draft_posts' => 'Draft Posts',
        'featured_posts' => 'Featured Posts',
        'posts_by_status' => 'Posts by Status',
        'recent_posts' => 'Recent Posts',
    ],

    // Engagement
    'engagement' => [
        'total_views' => 'Total Views',
        'total_views_description' => 'All time page views',
        'total_likes' => 'Total Likes',
        'total_likes_description' => 'All time likes received',
        'total_comments' => 'Total Comments',
        'total_comments_description' => 'All time comments received',
        'total_engagement' => 'Total Engagement',
        'total_engagement_description' => 'Combined likes and comments',
        'average_views' => 'Average Views',
        'average_views_description' => 'Average views per post',
        'average_likes' => 'Average Likes',
        'average_likes_description' => 'Average likes per post',
        'average_comments' => 'Average Comments',
        'average_comments_description' => 'Average comments per post',
        'average_engagement_rate' => 'Average Engagement Rate',
        'average_engagement_rate_description' => 'Average engagement percentage',
    ],

    // Performance
    'performance' => [
        'most_viewed' => 'Most Viewed',
        'most_liked' => 'Most Liked',
        'most_commented' => 'Most Commented',
        'most_popular' => 'Most Popular',
        'no_posts' => 'No posts available',
    ],

    // Authors
    'authors' => [
        'posts_count' => 'Posts Count',
        'posts' => 'Posts',
    ],

    // Media
    'media' => [
        'posts_with_media' => 'Posts with Media',
        'posts_with_media_description' => 'Posts that have media files',
        'posts_without_media' => 'Posts without Media',
        'posts_without_media_description' => 'Posts without media files',
        'posts_with_featured_image' => 'Posts with Featured Image',
        'posts_with_featured_image_description' => 'Posts with featured images',
        'posts_with_gallery' => 'Posts with Gallery',
        'posts_with_gallery_description' => 'Posts with image galleries',
        'total_media_files' => 'Total Media Files',
        'total_media_files_description' => 'Total media files across all posts',
        'average_media_per_post' => 'Average Media per Post',
        'average_media_per_post_description' => 'Average media files per post',
    ],

    // Empty states
    'empty_states' => [
        'no_posts' => 'No posts found',
        'no_published_posts' => 'No published posts',
        'no_draft_posts' => 'No draft posts',
        'no_featured_posts' => 'No featured posts',
    ],

    // Messages
    'messages' => [
        'created' => 'Post created successfully',
        'updated' => 'Post updated successfully',
        'deleted' => 'Post deleted successfully',
        'published' => 'Post published successfully',
        'unpublished' => 'Post unpublished successfully',
        'archived' => 'Post archived successfully',
        'featured' => 'Post featured successfully',
        'unfeatured' => 'Post unfeatured successfully',
    ],

    // Validation
    'validation' => [
        'title_required' => 'Title is required',
        'title_max' => 'Title must not exceed 255 characters',
        'slug_required' => 'Slug is required',
        'slug_unique' => 'Slug must be unique',
        'slug_alpha_dash' => 'Slug can only contain letters, numbers, dashes and underscores',
        'content_required' => 'Content is required',
        'status_required' => 'Status is required',
        'user_required' => 'Author is required',
        'published_at_required' => 'Published date is required',
        'meta_title_max' => 'Meta title must not exceed 60 characters',
        'meta_description_max' => 'Meta description must not exceed 160 characters',
    ],
];
