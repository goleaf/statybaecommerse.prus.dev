<?php

return [
    // General notifications
    'no_notifications' => 'No notifications',
    'check_later' => 'Check back later.',
    'mark_as_read' => 'Mark as read',
    'mark_all_as_read' => 'Mark all as read',
    'delete_notification' => 'Delete notification',
    'delete_all_notifications' => 'Delete all notifications',
    'notification_deleted' => 'Notification deleted',
    'all_notifications_deleted' => 'All notifications deleted',
    'all_marked_as_read' => 'All notifications marked as read',
    
    // Notification types
    'types' => [
        'info' => 'Information',
        'success' => 'Success',
        'warning' => 'Warning',
        'error' => 'Error',
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
    
    // Order notifications
    'order' => [
        'created' => 'New order created',
        'updated' => 'Order updated',
        'cancelled' => 'Order cancelled',
        'completed' => 'Order completed',
        'shipped' => 'Order shipped',
        'delivered' => 'Order delivered',
        'payment_received' => 'Payment received',
        'payment_failed' => 'Payment failed',
        'refund_processed' => 'Refund processed',
    ],
    
    // Product notifications
    'product' => [
        'created' => 'New product created',
        'updated' => 'Product updated',
        'deleted' => 'Product deleted',
        'low_stock' => 'Low stock alert',
        'out_of_stock' => 'Product out of stock',
        'back_in_stock' => 'Product back in stock',
        'price_changed' => 'Product price changed',
        'review_added' => 'Review added',
    ],
    
    // User notifications
    'user' => [
        'registered' => 'New user registered',
        'profile_updated' => 'Profile updated',
        'password_changed' => 'Password changed',
        'email_verified' => 'Email verified',
        'login' => 'Login',
        'logout' => 'Logout',
        'account_suspended' => 'Account suspended',
        'account_activated' => 'Account activated',
    ],
    
    // System notifications
    'system' => [
        'maintenance_started' => 'Maintenance started',
        'maintenance_completed' => 'Maintenance completed',
        'backup_created' => 'Backup created',
        'update_available' => 'Update available',
        'security_alert' => 'Security alert',
        'performance_issue' => 'Performance issue',
    ],
    
    // Payment notifications
    'payment' => [
        'processed' => 'Payment processed',
        'failed' => 'Payment failed',
        'refunded' => 'Payment refunded',
        'disputed' => 'Payment disputed',
        'chargeback' => 'Payment chargeback',
    ],
    
    // Shipping notifications
    'shipping' => [
        'label_created' => 'Shipping label created',
        'picked_up' => 'Package picked up',
        'in_transit' => 'Package in transit',
        'out_for_delivery' => 'Out for delivery',
        'delivered' => 'Package delivered',
        'delivery_failed' => 'Delivery failed',
        'returned' => 'Package returned',
    ],
    
    // Review notifications
    'review' => [
        'submitted' => 'Review submitted',
        'approved' => 'Review approved',
        'rejected' => 'Review rejected',
        'replied' => 'Reply to review',
    ],
    
    // Promotion notifications
    'promotion' => [
        'created' => 'New promotion created',
        'started' => 'Promotion started',
        'ended' => 'Promotion ended',
        'expiring_soon' => 'Promotion expiring soon',
    ],
    
    // Newsletter notifications
    'newsletter' => [
        'subscribed' => 'Newsletter subscribed',
        'unsubscribed' => 'Newsletter unsubscribed',
        'sent' => 'Newsletter sent',
    ],
    
    // Support notifications
    'support' => [
        'ticket_created' => 'Support ticket created',
        'ticket_updated' => 'Support ticket updated',
        'ticket_closed' => 'Support ticket closed',
        'message_received' => 'Message received',
        'response_sent' => 'Response sent',
    ],
    
    // Time formats
    'time' => [
        'just_now' => 'just now',
        'minutes_ago' => ':count min ago',
        'hours_ago' => ':count hr ago',
        'days_ago' => ':count day ago',
        'weeks_ago' => ':count wk ago',
        'months_ago' => ':count mo ago',
        'years_ago' => ':count yr ago',
    ],
];
