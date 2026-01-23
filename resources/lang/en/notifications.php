<?php

declare(strict_types=1);

return [
    // Actions
    'actions' => [
        'mark_as_read'         => 'Mark as read',
        'mark_as_unread'       => 'Mark as unread',
        'mark_all_read'        => 'Mark all as read',
        'delete_notification'  => 'Delete notification',
        'clear_all'            => 'Clear all',
        'refresh'              => 'Refresh',
        'filter_notifications' => 'Filter notifications',
    ],

    // Filters
    'filters' => [
        'all'         => 'All notifications',
        'unread_only' => 'Unread only',
        'by_type'     => 'By type',
        'recent'      => 'Recent',
        'urgent'      => 'Urgent',
    ],

    // Types
    'types' => [
        'order'      => 'Orders',
        'product'    => 'Products',
        'user'       => 'Users',
        'system'     => 'System',
        'payment'    => 'Payments',
        'shipping'   => 'Shipping',
        'review'     => 'Reviews',
        'promotion'  => 'Promotions',
        'newsletter' => 'Newsletter',
        'support'    => 'Support',
    ],

    // Messages
    'messages' => [
        'no_notifications'     => 'No notifications',
        'all_read'             => 'All notifications marked as read',
        'notification_deleted' => 'Notification deleted',
        'all_cleared'          => 'All notifications cleared',
        'loading'              => 'Loading notifications...',
        'error_loading'        => 'Error loading notifications',
        'mark_read_success'    => 'Notification marked as read',
        'mark_unread_success'  => 'Notification marked as unread',
    ],

    // Errors
    'errors' => [
        'unauthorized'  => 'You are not authorized to view notifications',
        'not_found'     => 'Notification not found',
        'action_failed' => 'Action failed',
        'rate_limit'    => 'Too many attempts. Please try again later',
    ],

    // Labels
    'labels' => [
        'notification_center' => 'Notification Center',
        'unread_count'        => 'Unread count',
        'created_at'          => 'Created',
        'read_at'             => 'Read',
        'urgent'              => 'Urgent',
        'normal'              => 'Normal',
        'priority'            => 'Priority',
    ],

    // Accessibility
    'aria' => [
        'notification_list'         => 'Notification list',
        'mark_as_read'              => 'Mark notification as read',
        'mark_as_unread'            => 'Mark notification as unread',
        'delete_notification'       => 'Delete notification',
        'notification_urgent'       => 'Urgent notification',
        'notification_read'         => 'Read notification',
        'notification_unread'       => 'Unread notification',
        'filter_by_type'            => 'Filter by type',
        'close_notification_center' => 'Close notification center',
    ],

    // Pagination
    'pagination' => [
        'showing'   => 'Showing',
        'of'        => 'of',
        'results'   => 'results',
        'per_page'  => 'per page',
        'load_more' => 'Load more',
        'no_more'   => 'No more notifications',
    ],
];
