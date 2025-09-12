# Notification System Documentation

## Overview

The notification system in this Laravel + Filament e-commerce application provides real-time notifications to admin users through the Filament admin panel. The system uses Laravel's built-in notification system with database storage.

## Features

- **Real-time notifications** displayed in the Filament admin panel
- **Database storage** for persistent notifications
- **Multiple notification types** (success, error, warning, info)
- **Admin-only notifications** for system events
- **Automatic polling** every 30 seconds for new notifications
- **Mark as read** functionality
- **Multilingual support** (Lithuanian and English)

## Components

### 1. Notification Classes

#### TestNotification
- **File**: `app/Notifications/TestNotification.php`
- **Purpose**: Non-queued notifications for immediate display
- **Channels**: Database only
- **Usage**: System notifications, alerts, updates

#### AdminNotification
- **File**: `app/Notifications/AdminNotification.php`
- **Purpose**: Queued notifications for admin users
- **Channels**: Database and Mail
- **Usage**: Important system announcements

#### LowStockAlert
- **File**: `app/Notifications/LowStockAlert.php`
- **Purpose**: Product stock level alerts
- **Channels**: Database and Mail
- **Usage**: Inventory management notifications

### 2. Notification Service

#### NotificationService
- **File**: `app/Services/NotificationService.php`
- **Purpose**: Centralized notification management
- **Methods**:
  - `sendToAdmins()` - Send to all admin users
  - `sendToUser()` - Send to specific user
  - `sendToUsers()` - Send to multiple users
  - `getUnreadCount()` - Get unread notification count
  - `markAllAsRead()` - Mark all notifications as read
  - `getRecentNotifications()` - Get recent notifications

### 3. Filament Configuration

The Filament admin panel is configured with:
- **Database notifications enabled**: `->databaseNotifications()`
- **Polling interval**: 30 seconds (`->databaseNotificationsPolling('30s')`)
- **Bell icon**: Automatically displayed in the top navigation

### 4. Commands

#### CreateTestNotifications
- **Command**: `php artisan notifications:create-test`
- **Purpose**: Create sample notifications for testing
- **Usage**: Testing the notification system

#### DemoNotifications
- **Command**: `php artisan notifications:demo`
- **Purpose**: Create realistic demo notifications
- **Usage**: Showcasing different notification types

#### ClearNotifications
- **Command**: `php artisan notifications:clear`
- **Purpose**: Clear all notifications from database
- **Usage**: Testing and maintenance

## Usage Examples

### Sending Notifications Programmatically

```php
use App\Services\NotificationService;

$notificationService = app(NotificationService::class);

// Send to all admins
$notificationService->sendToAdmins(
    'Naujas užsakymas',
    'Gautas naujas užsakymas #12345 už 125.50 €',
    'success'
);

// Send to specific user
$notificationService->sendToUser(
    $user,
    'Sveiki atvykę',
    'Jūsų paskyra sėkmingai sukurta',
    'info'
);
```

### Creating Custom Notifications

```php
use App\Notifications\TestNotification;

// Create and send notification
$user->notify(new TestNotification(
    'Custom Title',
    'Custom message content',
    'warning'
));
```

### Checking Notification Status

```php
use App\Services\NotificationService;

$notificationService = app(NotificationService::class);

// Get unread count
$unreadCount = $notificationService->getUnreadCount($user);

// Get recent notifications
$recentNotifications = $notificationService->getRecentNotifications($user, 10);

// Mark all as read
$notificationService->markAllAsRead($user);
```

## Database Schema

The notifications are stored in the `notifications` table:

```sql
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data JSON NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (notifiable_type, notifiable_id)
);
```

## Notification Data Structure

Each notification stores the following data:

```json
{
    "title": "Notification Title",
    "message": "Notification message content",
    "type": "success|error|warning|info",
    "sent_at": "2025-01-12T10:30:00.000000Z"
}
```

## Translation Support

Notifications support both Lithuanian and English languages. Translation keys are defined in:

- `resources/lang/lt/admin.php` - Lithuanian translations
- `resources/lang/en/admin.php` - English translations

### Available Translation Keys

```php
'notifications' => [
    'welcome_title' => 'Sveiki atvykę į valdymo skydą',
    'welcome_message' => 'Jūsų e-komercijos sistema sėkmingai sukonfigūruota ir paruošta naudojimui.',
    'system_update_title' => 'Sistemos atnaujinimas',
    'system_update_message' => 'Sistema buvo sėkmingai atnaujinta iki naujausios versijos.',
    'maintenance_title' => 'Priežiūros režimas',
    'maintenance_message' => 'Sistema bus nepasiekiama dėl planuotos priežiūros nuo 02:00 iki 04:00.',
    'security_alert_title' => 'Saugumo įspėjimas',
    'security_alert_message' => 'Aptiktas įtartinas veiksmas. Prašome patikrinti savo paskyrą.',
    'admin_message_footer' => 'Šis pranešimas buvo išsiųstas iš sistemos valdymo skydo.',
]
```

## Testing

### Creating Test Notifications

```bash
# Create basic test notifications
php artisan notifications:create-test

# Create demo notifications with realistic content
php artisan notifications:demo

# Clear all notifications
php artisan notifications:clear
```

### Manual Testing

1. **Access Admin Panel**: Navigate to `/admin`
2. **Login**: Use admin credentials
3. **Check Bell Icon**: Look for notification bell in top navigation
4. **Click Bell**: Click to open notifications panel
5. **View Notifications**: See all notifications with titles and messages
6. **Mark as Read**: Click individual notifications to mark as read
7. **Mark All as Read**: Use "Mark all as read" button

## Troubleshooting

### Common Issues

1. **Empty Notification Panel**
   - Check if notifications exist in database: `php artisan tinker --execute="echo \Illuminate\Notifications\DatabaseNotification::count();"`
   - Verify user has admin role
   - Check Filament configuration

2. **Notifications Not Appearing**
   - Ensure `databaseNotifications()` is enabled in Filament panel
   - Check polling interval configuration
   - Verify notification class implements correct methods

3. **Translation Issues**
   - Check translation files exist
   - Verify translation keys are correct
   - Ensure locale is set properly

### Debug Commands

```bash
# Check notification count
php artisan tinker --execute="echo \Illuminate\Notifications\DatabaseNotification::count();"

# Check user roles
php artisan tinker --execute="echo \App\Models\User::with('roles')->get()->pluck('name', 'email');"

# Check recent notifications
php artisan tinker --execute="\Illuminate\Notifications\DatabaseNotification::latest()->take(5)->get()->each(function(\$n) { echo \$n->data['title'] . PHP_EOL; });"
```

## Future Enhancements

- **Real-time updates** using WebSockets or Server-Sent Events
- **Email notifications** for critical alerts
- **Push notifications** for mobile devices
- **Notification categories** and filtering
- **Bulk notification management**
- **Notification templates** for common messages
- **User preferences** for notification types
- **Notification history** and archiving

## Security Considerations

- **Access Control**: Only admin users receive system notifications
- **Data Validation**: All notification data is validated before storage
- **Rate Limiting**: Consider implementing rate limiting for notification creation
- **Sanitization**: User input in notifications should be sanitized
- **Audit Trail**: Consider logging notification creation and access

## Performance Optimization

- **Database Indexing**: Ensure proper indexes on notification queries
- **Pagination**: Implement pagination for large notification lists
- **Cleanup**: Regular cleanup of old notifications
- **Caching**: Cache frequently accessed notification data
- **Queue Processing**: Use queues for heavy notification operations
