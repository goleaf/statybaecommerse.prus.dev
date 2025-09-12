<?php

return [
    // Basic Information
    'basic_information' => 'Basic Information',
    'notification_data' => 'Notification Data',
    'timestamps' => 'Timestamps',
    'notification_content' => 'Notification Content',
    'raw_data' => 'Raw Data',

    // Fields
    'id' => 'ID',
    'type' => 'Type',
    'notifiable_type' => 'Notifiable Type',
    'notifiable_id' => 'Notifiable ID',
    'data' => 'Data',
    'key' => 'Key',
    'value' => 'Value',
    'add_field' => 'Add Field',
    'title' => 'Title',
    'message' => 'Message',
    'notification_type' => 'Notification Type',
    'color' => 'Color',
    'tags' => 'Tags',
    'urgent' => 'Urgent',
    'attachment' => 'Attachment',
    'read_at' => 'Read At',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'read_status' => 'Read Status',

    // Status
    'read' => 'Read',
    'unread' => 'Unread',
    'not_read' => 'Not Read',
    'all_notifications' => 'All Notifications',
    'normal' => 'Normal',

    // Types
    'types' => [
        'order' => 'Order',
        'product' => 'Product',
        'user' => 'User',
        'system' => 'System',
        'payment' => 'Payment',
        'shipping' => 'Shipping',
        'review' => 'Review',
        'promotion' => 'Promotion',
        'newsletter' => 'Newsletter',
        'support' => 'Support',
    ],

    // Actions
    'actions' => 'Actions',
    'view' => 'View',
    'mark_as_read' => 'Mark as Read',
    'mark_as_unread' => 'Mark as Unread',
    'duplicate' => 'Duplicate',
    'delete' => 'Delete',
    'mark_all_as_read' => 'Mark All as Read',
    'mark_all_as_unread' => 'Mark All as Unread',
    'delete_old' => 'Delete Old',
    'delete_selected' => 'Delete Selected',
    'bulk_actions' => 'Bulk Actions',
    'export' => 'Export',
    'cleanup_old' => 'Cleanup Old',

    // Confirmations
    'mark_as_read_confirmation' => 'Are you sure you want to mark this notification as read?',
    'mark_as_unread_confirmation' => 'Are you sure you want to mark this notification as unread?',
    'duplicate_confirmation' => 'Are you sure you want to duplicate this notification?',
    'mark_all_as_read_confirmation' => 'Are you sure you want to mark all notifications as read?',
    'mark_all_as_unread_confirmation' => 'Are you sure you want to mark all notifications as unread?',
    'delete_old_confirmation' => 'Are you sure you want to delete old notifications (older than 30 days)?',
    'cleanup_old_confirmation' => 'Are you sure you want to cleanup old notifications (older than 30 days)?',

    // Messages
    'marked_as_read' => 'Notification marked as read',
    'marked_as_unread' => 'Notification marked as unread',
    'duplicated' => 'Notification duplicated',
    'cleanup_completed' => 'Cleaned up :count old notifications',
    'export_started' => 'Export started',

    // Stats
    'total_notifications' => 'Total Notifications',
    'unread_notifications' => 'Unread Notifications',
    'urgent_notifications' => 'Urgent Notifications',
    'today_notifications' => 'Today\'s Notifications',
    'this_week_notifications' => 'This Week\'s Notifications',
    'this_month_notifications' => 'This Month\'s Notifications',
    'all_time' => 'All Time',
    'requires_attention' => 'Requires Attention',
    'high_priority' => 'High Priority',
    'created_today' => 'Created Today',
    'created_this_week' => 'Created This Week',
    'created_this_month' => 'Created This Month',

    // Filters
    'read_status' => 'Read Status',
    'urgent_status' => 'Urgent Status',
    'created_from' => 'Created From',
    'created_until' => 'Created Until',
    'read_from' => 'Read From',
    'read_until' => 'Read Until',

    // Tabs
    'all_notifications' => 'All Notifications',
    'unread_notifications' => 'Unread Notifications',
    'read_notifications' => 'Read Notifications',
    'urgent_notifications' => 'Urgent Notifications',
    'today_notifications' => 'Today\'s Notifications',
    'this_week_notifications' => 'This Week\'s Notifications',

    // Charts
    'notification_count' => 'Notification Count',
    'notification_types_distribution' => 'Notification Types Distribution',
    'notification_trends' => 'Notification Trends (Last 30 Days)',
    'recent_notifications' => 'Recent Notifications',

    // Specific notification types
    'order' => [
        'created' => 'New Order',
        'updated' => 'Order Updated',
        'cancelled' => 'Order Cancelled',
        'completed' => 'Order Completed',
        'shipped' => 'Order Shipped',
        'delivered' => 'Order Delivered',
        'payment_received' => 'Payment Received',
        'payment_failed' => 'Payment Failed',
        'refund_processed' => 'Refund Processed',
    ],

    'product' => [
        'created' => 'New Product',
        'updated' => 'Product Updated',
        'deleted' => 'Product Deleted',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'back_in_stock' => 'Back in Stock',
        'price_changed' => 'Price Changed',
        'review_added' => 'Review Added',
    ],

    'user' => [
        'registered' => 'New User',
        'profile_updated' => 'Profile Updated',
        'password_changed' => 'Password Changed',
        'email_verified' => 'Email Verified',
        'login' => 'Login',
        'logout' => 'Logout',
        'account_suspended' => 'Account Suspended',
        'account_activated' => 'Account Activated',
    ],

    'system' => [
        'maintenance_started' => 'Maintenance Started',
        'maintenance_completed' => 'Maintenance Completed',
        'backup_created' => 'Backup Created',
        'update_available' => 'Update Available',
        'security_alert' => 'Security Alert',
        'performance_issue' => 'Performance Issue',
    ],

    'admin' => [
        'admin_message_footer' => 'This message was sent from the system administration.',
    ],

    'low_stock' => [
        'alert_title' => 'Low Stock Alert',
        'alert_message' => 'Product :name is running low on stock (:stock units remaining)',
        'threshold_message' => 'Threshold: :threshold units',
        'action_message' => 'Please restock this product to avoid stockouts.',
    ],
];
