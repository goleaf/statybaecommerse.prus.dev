# Notification System Documentation

## Overview

The notification system provides a comprehensive solution for managing user notifications in the application. It includes both admin panel management and API endpoints for frontend integration.

## Features

### Admin Panel Features
- **Comprehensive Resource Management**: Full CRUD operations for notifications
- **Advanced Filtering**: Filter by type, read status, urgency, date ranges
- **Bulk Operations**: Mark multiple notifications as read/unread, delete old notifications
- **Rich UI Components**: 4 comprehensive widgets (stats, charts, recent notifications)
- **Real-time Updates**: 30-second polling for live data
- **Session Persistence**: Filters, sorting, and search state maintained

### API Features
- **RESTful Endpoints**: Complete API for notification management
- **Authentication**: Protected by Sanctum authentication
- **Pagination**: Efficient data loading with pagination
- **Search**: Full-text search across notification content
- **Statistics**: User notification statistics

## Components

### Models

#### Notification Model (`app/Models/Notification.php`)
- Extends Laravel's `DatabaseNotification`
- Comprehensive scopes for filtering
- Rich accessors for formatted data
- Utility methods for common operations

**Key Methods:**
- `markAsRead()` / `markAsUnread()`
- `duplicate()`
- `getNotificationTypeColor()` / `getNotificationTypeIcon()`
- `getTimeAgo()` / `getReadTimeAgo()`

**Scopes:**
- `read()` / `unread()`
- `urgent()` / `normal()`
- `byType($type)`
- `forUser($userId)`
- `recent($days)` / `old($days)`

### Services

#### NotificationService (`app/Services/NotificationService.php`)
Centralized service for notification management.

**Key Methods:**
- `createNotification()` - Create new notifications
- `createOrderNotification()` - Order-specific notifications
- `createProductNotification()` - Product-specific notifications
- `markAsRead()` / `markAsUnread()` - Mark individual notifications
- `markAllAsReadForUser()` / `markAllAsUnreadForUser()` - Bulk operations
- `getUserNotifications()` - Get paginated user notifications
- `getUserNotificationStats()` - Get user statistics

### Filament Resources

#### NotificationResource (`app/Filament/Resources/NotificationResource.php`)
Complete admin resource with:
- **Form**: Organized sections with collapsible panels
- **Table**: 15+ columns with badges, icons, colors, tags
- **Filters**: Type, read status, urgency, date ranges
- **Actions**: View, mark as read/unread, duplicate, delete
- **Bulk Actions**: Mass operations for efficiency

#### Pages
- **ListNotifications**: Header actions, tabs, integrated widgets
- **ViewNotification**: Comprehensive infolist with sections

### Widgets

#### NotificationStatsWidget
Displays 6 stat cards:
- Total notifications
- Unread notifications
- Urgent notifications
- Today's notifications
- This week's notifications
- This month's notifications

#### NotificationTypeChartWidget
Doughnut chart showing distribution of notification types with:
- Color-coded segments
- Percentage tooltips
- Responsive design

#### NotificationTrendChartWidget
Line chart showing trends over last 30 days:
- Total notifications
- Unread notifications
- Read notifications
- Interactive tooltips

#### RecentNotificationsWidget
Table widget showing 10 most recent notifications with:
- Quick actions
- Real-time updates
- Direct links to full view

### API Controller

#### NotificationController (`app/Http/Controllers/Api/NotificationController.php`)
RESTful API endpoints:

**Endpoints:**
- `GET /api/notifications` - Get user notifications
- `GET /api/notifications/stats` - Get statistics
- `GET /api/notifications/search` - Search notifications
- `POST /api/notifications/mark-all-read` - Mark all as read
- `POST /api/notifications/mark-all-unread` - Mark all as unread
- `GET /api/notifications/{id}` - Get specific notification
- `POST /api/notifications/{id}/mark-read` - Mark as read
- `POST /api/notifications/{id}/mark-unread` - Mark as unread
- `DELETE /api/notifications/{id}` - Delete notification

### Notification Classes

#### OrderNotification (`app/Notifications/OrderNotification.php`)
Handles order-related notifications with email support.

#### ProductNotification (`app/Notifications/ProductNotification.php`)
Manages product-related notifications.

#### SystemNotification (`app/Notifications/SystemNotification.php`)
Handles system-wide notifications.

## Usage Examples

### Creating Notifications

```php
use App\Services\NotificationService;

$notificationService = app(NotificationService::class);

// Create order notification
$notificationService->createOrderNotification(
    $user,
    'created',
    ['id' => 1, 'number' => 'ORD-001'],
    false
);

// Create product notification
$notificationService->createProductNotification(
    $user,
    'low_stock',
    ['id' => 1, 'name' => 'Test Product'],
    true
);

// Create system notification
$notificationService->createSystemNotification(
    $user,
    'maintenance_started',
    [],
    true
);
```

### API Usage

```javascript
// Get user notifications
const response = await fetch('/api/notifications', {
    headers: {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
    }
});

// Mark notification as read
await fetch('/api/notifications/1/mark-read', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
    }
});

// Get statistics
const stats = await fetch('/api/notifications/stats', {
    headers: {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
    }
});
```

### Testing

```bash
# Create test notifications
php artisan notifications:test --user-id=1

# Seed database with sample notifications
php artisan db:seed --class=NotificationSeeder
```

## Configuration

### Translation Files
- `lang/lt/notifications.php` - Lithuanian translations
- `lang/en/notifications.php` - English translations

### Factory
- `database/factories/NotificationFactory.php` - Test data generation

### Seeder
- `database/seeders/NotificationSeeder.php` - Sample data population

## Security

- All API endpoints are protected by Sanctum authentication
- Users can only access their own notifications
- Admin panel access controlled by Filament permissions
- Input validation on all endpoints

## Performance

- Efficient database queries with proper indexing
- Pagination for large datasets
- Real-time updates with 30-second polling
- Background job processing for email notifications
- Caching for frequently accessed data

## Customization

### Adding New Notification Types

1. Add type to translation files
2. Create notification class
3. Add to NotificationService
4. Update factory for testing

### Customizing UI

1. Modify widget components
2. Update table columns
3. Add new filters
4. Customize actions

### API Extensions

1. Add new endpoints to controller
2. Update routes
3. Add validation
4. Update documentation

## Troubleshooting

### Common Issues

1. **Notifications not appearing**: Check user authentication and permissions
2. **Real-time updates not working**: Verify polling configuration
3. **API errors**: Check authentication tokens and request format
4. **Performance issues**: Review database queries and indexing

### Debug Commands

```bash
# Check notification count
php artisan tinker
>>> App\Models\Notification::count()

# Test notification creation
php artisan notifications:test

# Clear old notifications
php artisan tinker
>>> App\Models\Notification::cleanupOld(30)
```

## Future Enhancements

- Push notifications via WebSocket
- Email templates customization
- Notification preferences
- Advanced analytics
- Mobile app integration
- Multi-language support
- Notification scheduling
- User notification preferences
- Advanced filtering options
- Export functionality
